<?php
session_start();
require_once 'conexao.php';

// Redireciona para login se tentar aceder sem conta
if (!isset($_SESSION['utilizador_id'])) {
    header("Location: spotfinder_auth.php");
    exit;
}

$utilizador_id = $_SESSION['utilizador_id'];

// INNER JOIN: Vai buscar os dados da tabela 'compras' cruzados com os dados do 'evento'
$query = "
    SELECT c.*, e.titulo, e.data_evento, e.localizacao, e.imagem_url, e.categoria 
    FROM compras c
    INNER JOIN eventos e ON c.evento_id = e.id
    WHERE c.utilizador_id = ?
    ORDER BY c.data_compra DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute([$utilizador_id]);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Somar o total de dinheiro gasto
$total_gasto = 0;
foreach ($compras as $c) {
    $total_gasto += $c['valor_total'];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>O Meu Carrinho - SpotFinder</title>
<link rel="stylesheet" href="css.css">
<style>
  /* Pequenos estilos exclusivos para a vista de faturação/bilhetes */
  .cart-item { display: flex; gap: 20px; background: #fff; border: 1px solid var(--gray-200); padding: 16px; border-radius: var(--radius); margin-bottom: 16px; align-items: center; transition: box-shadow 0.2s; }
  .cart-item:hover { box-shadow: var(--shadow-md); }
  .cart-item img { width: 100px; height: 100px; object-fit: cover; border-radius: var(--radius-sm); }
  .cart-info { flex: 1; }
  .cart-price { font-size: 20px; font-weight: 800; color: var(--black); }
  .cart-total-box { background: var(--black); color: var(--white); padding: 32px; border-radius: var(--radius); margin-top: 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
</style>
</head>
<body style="background: var(--gray-50); padding-top: 80px;">

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

<section class="section" style="max-width: 800px; margin: 0 auto;">
  <div class="section-header">
    <h2>Os Meus Bilhetes</h2>
    <p>Aqui tens o histórico das tuas compras e os detalhes de admissão.</p>
  </div>

  <?php if (count($compras) == 0): ?>
    <div class="no-results visible" style="margin-top: 40px;">
      <p style="font-size: 18px; color: var(--gray-600);">O teu carrinho está vazio. Ainda não compraste nenhum bilhete.</p>
      <a href="spotfinder.php" class="btn-load" style="display: inline-block; margin-top: 20px;">Explorar Eventos</a>
    </div>
  <?php else: ?>
    
    <?php foreach ($compras as $item): ?>
        <div class="cart-item">
          <img src="<?= htmlspecialchars($item['imagem_url']) ?>" alt="Capa do Evento">
          <div class="cart-info">
            <span class="badge" style="position: static; display: inline-block; margin-bottom: 8px; font-size: 10px;"><?= htmlspecialchars($item['categoria']) ?></span>
            <h3 style="margin-bottom: 4px; font-size: 18px;"><?= htmlspecialchars($item['titulo']) ?></h3>
            <p style="font-size: 13px; color: var(--gray-600);">📍 <?= htmlspecialchars($item['localizacao']) ?> | 📅 <?= htmlspecialchars($item['data_evento']) ?></p>
            <p style="font-size: 12px; color: var(--gray-400); margin-top: 8px;">Referência da Compra: #<?= str_pad($item['id'], 5, '0', STR_PAD_LEFT) ?> &middot; Data: <?= date('d/m/Y H:i', strtotime($item['data_compra'])) ?></p>
          </div>
          <div class="cart-price">
            <?= ($item['valor_total'] == 0) ? 'Gratuito' : '€' . number_format($item['valor_total'], 2, ',', '.') ?>
          </div>
        </div>
    <?php endforeach; ?>

    <div class="cart-total-box">
        <div>
            <p style="font-size: 14px; color: rgba(255,255,255,0.6); margin-bottom: 4px; font-weight: 600; text-transform: uppercase;">Total Faturado</p>
            <h2 style="font-size: 36px; margin: 0;">€<?= number_format($total_gasto, 2, ',', '.') ?></h2>
        </div>
        <button class="nav-cta" style="background: var(--yellow); color: var(--black); padding: 14px 24px; font-size: 15px;" onclick="alert('Esta funcionalidade irá descarregar um PDF com os QR Codes dos bilhetes!')">
          Descarregar Bilhetes
        </button>
    </div>
  <?php endif; ?>
  
</section>
</body>
</html>