<?php
session_start();
require_once 'conexao.php';

// 1. Proteger a página: Redireciona para o login se não tiver sessão iniciada
if (!isset($_SESSION['utilizador_id'])) {
    header("Location: spotfinder_auth.php");
    exit;
}

$utilizador_id = $_SESSION['utilizador_id'];

// 2. Ir buscar APENAS os eventos que este utilizador específico guardou
$query = "
    SELECT e.* FROM eventos e
    INNER JOIN favoritos f ON e.id = f.evento_id
    WHERE f.utilizador_id = ?
    ORDER BY f.adicionado_em DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$utilizador_id]);
$eventos_favoritos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Os Meus Favoritos - SpotFinder</title>
<link rel="stylesheet" href="css.css">
</head>
<body style="background: var(--gray-50); padding-top: 80px;">

<div class="toast" id="toast"></div>

<nav id="mainNav" class="scrolled">
  <a href="spotfinder.php" class="nav-brand">
    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    SpotFinder
  </a>
  <div style="display: flex; align-items: center; gap: 12px;">
    <span class="desktop-only" style="font-size: 14px; font-weight: 600; color: var(--gray-600);">
      Olá, <?= htmlspecialchars(explode(' ', $_SESSION['utilizador_nome'])[0]) ?>
    </span>
    
    <a href="favoritos.php" style="font-size: 14px; font-weight: 600; text-decoration: none; white-space: nowrap;">
      Favoritos
    </a>
    
    <a href="spotfinder.php" class="nav-cta" style="background: var(--white); color: var(--black); border: 1px solid var(--gray-200); white-space: nowrap; padding: 8px 12px;">
      Voltar
    </a>
  </div>
</nav>

<section class="section">
  <div class="section-header">
    <h2>Os Meus Favoritos</h2>
    <p>A tua lista pessoal de spots e eventos guardados.</p>
  </div>

  <?php if (count($eventos_favoritos) == 0): ?>
    <div class="no-results visible" style="margin-top: 40px;">
      <p style="font-size: 18px; color: var(--gray-600);">Ainda não guardaste nenhum evento.</p>
      <a href="spotfinder.php" class="btn-load" style="display: inline-block; margin-top: 20px;">Descobrir Spots</a>
    </div>
  
  <?php else: ?>
    <div class="events-grid" id="eventsGrid">
      <?php foreach ($eventos_favoritos as $evento): ?>
          <?php 
              $preco_formatado = ($evento['preco'] == 0) ? 'Gratuito' : '€' . number_format($evento['preco'], 2, ',', '.');
              $cor_badge = '#111'; 
              if ($evento['categoria'] == 'Concerto') $cor_badge = '#3b82f6';
              if ($evento['categoria'] == 'Exposição') $cor_badge = '#a855f7';
              if ($evento['categoria'] == 'Teatro') $cor_badge = '#ef4444';
          ?>
          <div class="event-card" data-id="<?= htmlspecialchars($evento['id']) ?>">
            <div class="event-img">
              <img src="<?= htmlspecialchars($evento['imagem_url']) ?>" alt="<?= htmlspecialchars($evento['titulo']) ?>"/>
              <span class="badge" style="background: <?= $cor_badge ?>;"><?= htmlspecialchars($evento['categoria']) ?></span>
              
              <button class="fav-btn liked" onclick="removerFavoritoVisual(this)">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              </button>
            </div>
            <div class="event-body">
              <h3><?= htmlspecialchars($evento['titulo']) ?></h3>
              <div class="event-meta">
                <div class="event-meta-row"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> <?= htmlspecialchars($evento['data_evento']) ?></div>
                <div class="event-meta-row"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> <?= htmlspecialchars($evento['localizacao']) ?></div>
              </div>
              <div class="event-footer">
                <div><div class="price-label"><?= ($evento['preco'] == 0) ? 'Entrada' : 'A partir de' ?></div><div class="price"><?= htmlspecialchars($preco_formatado) ?></div></div>
                <button class="btn-details">Ver Bilhetes</button>
              </div>
            </div>
          </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<script>
// Função Toast
let toastTimer;
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 2800);
}

// Lógica para remover o evento da base de dados e fazê-lo desaparecer do ecrã
async function removerFavoritoVisual(btn) {
  const card = btn.closest('.event-card');
  const eventId = card.dataset.id;

  try {
    const response = await fetch('processa_favorito.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'evento_id=' + eventId
    });
    
    const data = await response.json();

    if (data.status === 'sucesso' && data.acao === 'removido') {
      // Efeito visual suave de remoção (fade out + encolher)
      card.style.transition = "opacity 0.3s ease, transform 0.3s ease";
      card.style.opacity = "0";
      card.style.transform = "scale(0.9)";
      
      // Remove