<?php
session_start();
require_once 'conexao.php';

// 1. Procurar os eventos para a listagem principal
$stmt = $pdo->query("SELECT * FROM eventos ORDER BY criado_em DESC");
$eventos_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── NOVO: PROCURAR OS FAVORITOS DO UTILIZADOR LOGADO ──
$favoritos_utilizador = [];
if (isset($_SESSION['utilizador_id'])) {
    $stmt_fav = $pdo->prepare("SELECT evento_id FROM favoritos WHERE utilizador_id = ?");
    $stmt_fav->execute([$_SESSION['utilizador_id']]);
    // A função FETCH_COLUMN vai criar uma lista simples só com os números dos IDs (ex: [1, 4, 5])
    $favoritos_utilizador = $stmt_fav->fetchAll(PDO::FETCH_COLUMN); 
}

// 2. Preparar um array formatado especificamente para o JavaScript do mapa
$eventos_para_mapa = [];
foreach ($eventos_db as $row) {
    $eventos_para_mapa[] = [
        'id'    => (string)$row['id'],
        'name'  => $row['titulo'],
        'lat'   => (float)$row['latitude'],
        'lng'   => (float)$row['longitude'],
        'cat'   => $row['categoria'],
        'date'  => $row['data_evento'],
        'price' => ($row['preco'] == 0) ? 'Gratuito' : number_format($row['preco'], 2, ',', ''),
        'loc'   => $row['localizacao'],
        'img'   => $row['imagem_url']
    ];
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SpotFinder &ndash; Descobre os Melhores Eventos em Portugal</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<link rel="stylesheet" href="css.css">
</head>
<body>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<!-- MODAL -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal">
    <button class="modal-close" onclick="closeModal()">&#x2715;</button>
    <div class="modal-badge" id="modalBadge">Festival</div>
    <h2 id="modalTitle">Evento</h2>
    <div class="modal-meta">
      <div class="modal-meta-row">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span id="modalDate">-</span>
      </div>
      <div class="modal-meta-row">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <span id="modalLocation">-</span>
      </div>
    </div>
    <div class="modal-price" id="modalPrice">&#x20ac;0,00 <small>por pessoa</small></div>
    <button class="btn-buy" onclick="buyTicket()">Comprar Bilhete &#x2192;</button>
  </div>
</div>

<!-- NAV -->
<nav id="mainNav">
  <a href="spotfinder.php" class="nav-brand">
    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
      <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    SpotFinder
  </a>
  
  <ul class="nav-links desktop-only">
    <li><a href="#eventos">Eventos</a></li>
    <li><a href="#categorias">Categorias</a></li>
    <li><a href="#mapa">Mapa</a></li>
    <li><a href="#sobre">Sobre</a></li>
  </ul>
  
  <div class="desktop-only" style="display: flex; align-items: center; gap: 16px; color: inherit;">
    <?php if (isset($_SESSION['utilizador_id'])): ?>
      <span style="font-size: 14px; font-weight: 600; color: var(--gray-600);">
        Olá, <?= htmlspecialchars(explode(' ', $_SESSION['utilizador_nome'])[0]) ?>
      </span>
      <a href="favoritos.php" style="font-size: 14px; font-weight: 600; text-decoration: none;">❤️ Favoritos</a>
      <a href="carrinho.php" style="font-size: 14px; font-weight: 600; text-decoration: none;">🛒 Carrinho</a>
      <a href="logout.php" class="nav-cta" style="background: var(--gray-200); color: var(--black);">Sair</a>
    <?php else: ?>
      <a href="spotfinder_auth.php" class="nav-cta" onclick="showToast('👤 A abrir a tua conta...')">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
        Entrar
      </a>
    <?php endif; ?>
  </div>

  <button class="hamburger-btn" onclick="toggleMobileMenu()">
    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
  </button>
</nav>

<div class="mobile-menu-overlay" id="mobileMenu">
  <div class="mobile-menu-content">
    <button class="close-menu-btn" onclick="toggleMobileMenu()">✕</button>
    
    <ul class="mobile-links">
      <li><a href="#eventos" onclick="toggleMobileMenu()">Eventos</a></li>
      <li><a href="#categorias" onclick="toggleMobileMenu()">Categorias</a></li>
      <li><a href="#mapa" onclick="toggleMobileMenu()">Mapa</a></li>
      <li><a href="#sobre" onclick="toggleMobileMenu()">Sobre</a></li>
    </ul>

    <div class="mobile-user-actions">
      <?php if (isset($_SESSION['utilizador_id'])): ?>
        <div class="mobile-user-greeting">Olá, <?= htmlspecialchars(explode(' ', $_SESSION['utilizador_nome'])[0]) ?></div>
        <a href="favoritos.php" class="mobile-action-link">❤️ Os Meus Favoritos</a>
        <a href="carrinho.php" class="mobile-action-link">🛒 O Meu Carrinho</a>
        <a href="logout.php" class="btn-logout-mobile">Sair da Conta</a>
      <?php else: ?>
        <a href="spotfinder_auth.php" class="btn-login-mobile">Entrar / Criar Conta</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg" id="heroBg"></div>
  <div class="hero-overlay"></div>
  <div class="hero-content">
    <div class="hero-badge"> 816 eventos acontecendo agora</div>
    <h1>Encontra os Melhores<br><span>Spots</span> em Portugal</h1>
    <p>Concertos, festivais, exposi&#xe7;&#xf5;es e muito mais.</p>


    <div class="search-box">
      <div class="search-row">
        <div class="search-input">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="text" id="searchQuery" placeholder="Procurar eventos..." oninput="filterEvents()"/>
        </div>
        <div class="search-input">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          <input type="text" id="searchLocation" placeholder="Localiza&ccedil;&atilde;o" oninput="filterEvents()"/>
        </div>
        <div class="search-input">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <input type="text" id="searchDate" placeholder="dd/mm/aaaa"/>
        </div>
      </div>
      <button class="btn-search" onclick="doSearch()">Procurar Eventos</button>
    </div>

    <div class="populares">
      <span>Populares:</span>
      <button class="tag" onclick="quickSearch('Rock in Rio')">Rock in Rio Lisboa</button>
      <button class="tag" onclick="quickSearch('NOS Alive')">NOS Alive</button>
      <button class="tag" onclick="quickSearch('Serralves')">Serralves em Festa</button>
      <button class="tag" onclick="quickSearch('Teatro')">Teatro Nacional</button>
    </div>
  </div>

  <div class="scroll-hint">
    <span>Explorar</span>
    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
  </div>
</section>

<!-- STATS -->
<div class="stats-bar">
  <div class="stat-item"><div class="stat-num">816</div><div class="stat-label">Eventos ativos</div></div>
  <div class="stat-item"><div class="stat-num">18</div><div class="stat-label">Cidades</div></div>
  <div class="stat-item"><div class="stat-num">6</div><div class="stat-label">Utilizadores</div></div>
  <div class="stat-item"><div class="stat-num">100%</div><div class="stat-label">Satisfa&ccedil;&atilde;o</div></div>
</div>

<!-- CATEGORIES -->
<section class="section categories-bg" id="categorias">
  <div class="section-header">
    <h2>Explorar por Categoria</h2>
    <p>Descobre eventos organizados por tipo de experi&ecirc;ncia</p>
  </div>
  <div class="cat-grid">
    <div class="cat-card" onclick="filterByCategory('Concerto')" data-cat="Concerto">
      <div class="cat-icon" style="background:#dbeafe;">&#127925;</div>
      <h3>Concertos</h3><p>234 eventos</p>
    </div>
    <div class="cat-card" onclick="filterByCategory('Exposi&ccedil;&atilde;o')" data-cat="Exposi&ccedil;&atilde;o">
      <div class="cat-icon" style="background:#f3e8ff;">&#127912;</div>
      <h3>Exposi&ccedil;&otilde;es</h3><p>156 eventos</p>
    </div>
    <div class="cat-card" onclick="filterByCategory('Teatro')" data-cat="Teatro">
      <div class="cat-icon" style="background:#fee2e2;">&#127917;</div>
      <h3>Teatro</h3><p>89 eventos</p>
    </div>
    <div class="cat-card" onclick="filterByCategory('Festival')" data-cat="Festival">
      <div class="cat-icon" style="background:#fce7f3;">&#127881;</div>
      <h3>Festivais</h3><p>67 eventos</p>
    </div>
  </div>
</section>

<!-- EVENTS -->
<section class="section" id="eventos">
  <div class="events-header-row">
    <div class="section-header" style="margin-bottom:0">
      <h2>Eventos em Destaque</h2>
      <p>Os melhores eventos acontecendo agora em Portugal</p>
    </div>
    <button class="btn-filter">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M7 8h10M11 12h2M11 16h2"/></svg>
      Filtrar
    </button>
  </div>

  <div class="filter-tabs">
    <button class="filter-tab active" onclick="setTab(this,'todos')">Todos</button>
    <button class="filter-tab" onclick="setTab(this,'Festival')">Festivais</button>
    <button class="filter-tab" onclick="setTab(this,'Concerto')">Concertos</button>
    <button class="filter-tab" onclick="setTab(this,'Exposição')">Exposições</button>
    <button class="filter-tab" onclick="setTab(this,'Teatro')">Teatro</button>
    <button class="filter-tab" onclick="setTab(this,'Gastronomia')">Gastronomia</button>
  </div>
  <div class="no-results" id="noResults">🔍 Nenhum evento encontrado. Tenta outra pesquisa.</div>

  <div class="events-featured" id="featuredRow">
    <?php 
    $destaques = array_slice($eventos_db, 0, 2);
    foreach ($destaques as $index => $evento): 
        $preco_formatado = ($evento['preco'] == 0) ? 'Gratuito' : '€' . number_format($evento['preco'], 2, ',', '.');
        $cor_badge = '#111'; 
        if ($evento['categoria'] == 'Concerto') $cor_badge = '#3b82f6';
        if ($evento['categoria'] == 'Exposição') $cor_badge = '#a855f7';
        if ($evento['categoria'] == 'Teatro') $cor_badge = '#ef4444';
        $classe_img = ($index === 0) ? 'event-img event-img-lg' : 'event-img';
    ?>
    <div class="event-card" data-id="<?= htmlspecialchars($evento['id']) ?>" data-cat="<?= htmlspecialchars($evento['categoria']) ?>" data-name="<?= htmlspecialchars($evento['titulo']) ?>" data-loc="<?= htmlspecialchars($evento['localizacao']) ?>" data-date="<?= htmlspecialchars($evento['data_evento']) ?>" data-price="<?= htmlspecialchars($preco_formatado) ?>">
      <div class="<?= $classe_img ?>">
        <img src="<?= htmlspecialchars($evento['imagem_url']) ?>" alt="<?= htmlspecialchars($evento['titulo']) ?>" onerror="this.style.display='none'"/>
        <span class="badge" style="background: <?= $cor_badge ?>;"><?= htmlspecialchars($evento['categoria']) ?></span>
        <button class="fav-btn <?= in_array($evento['id'], $favoritos_utilizador) ? 'liked' : '' ?>" onclick="toggleFav(this)">
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
          <button class="btn-details" onclick="openModal(this)">Ver Detalhes</button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="events-grid" id="eventsGrid">
    <?php 
    $restantes = array_slice($eventos_db, 2);
    foreach ($restantes as $evento): 
        $preco_formatado = ($evento['preco'] == 0) ? 'Gratuito' : '€' . number_format($evento['preco'], 2, ',', '.');
        $cor_badge = '#111'; 
        if ($evento['categoria'] == 'Concerto') $cor_badge = '#3b82f6';
        if ($evento['categoria'] == 'Exposição') $cor_badge = '#a855f7';
        if ($evento['categoria'] == 'Teatro') $cor_badge = '#ef4444';
    ?>
    <div class="event-card" data-id="<?= htmlspecialchars($evento['id']) ?>" data-cat="<?= htmlspecialchars($evento['categoria']) ?>" data-name="<?= htmlspecialchars($evento['titulo']) ?>" data-loc="<?= htmlspecialchars($evento['localizacao']) ?>" data-date="<?= htmlspecialchars($evento['data_evento']) ?>" data-price="<?= htmlspecialchars($preco_formatado) ?>">
      <div class="event-img">
        <img src="<?= htmlspecialchars($evento['imagem_url']) ?>" alt="<?= htmlspecialchars($evento['titulo']) ?>"/>
        <span class="badge" style="background: <?= $cor_badge ?>;"><?= htmlspecialchars($evento['categoria']) ?></span>
        <button class="fav-btn <?= in_array($evento['id'], $favoritos_utilizador) ? 'liked' : '' ?>" onclick="toggleFav(this)">
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
          <button class="btn-details" onclick="openModal(this)">Ver Detalhes</button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="load-more-wrap">
    <button class="btn-load" onclick="showToast('✅ Mais eventos carregados!')">Carregar Mais Eventos</button>
  </div>
</section>

<!-- MAP -->
<section class="map-section" id="mapa">
  <div class="section-header">
    <h2>Explorar via Mapa Interativo</h2>
    <p>Clique nos pins para descobrir os detalhes e reservar os melhores spots perto de si</p>
  </div>
  <!-- Contentor do mapa com wrapper para sombras modernas -->
  <div class="map-wrapper">
    <div id="map"></div>
  </div>
</section>

<!-- FOOTER -->
<footer id="sobre">
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="footer-brand-name">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        SpotFinder
      </div>
      <p>Descobre os melhores eventos culturais e de lazer em Portugal</p>
    </div>
    <div class="footer-col">
      <h4>Explorar</h4>
      <ul>
        <li><a href="#eventos">Eventos</a></li>
        <li><a href="#categorias">Categorias</a></li>
        <li><a href="#mapa">Mapa</a></li>
        <li><a href="#">Favoritos</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Sobre</h4>
      <ul>
        <li><a href="#">Sobre N&oacute;s</a></li>
        <li><a href="#">Como Funciona</a></li>
        <li><a href="#">Parceiros</a></li>
        <li><a href="#">Carreiras</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Contacto</h4>
      <div class="footer-contact">
        <div class="footer-contact-row">
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          info@spotfinder.pt
        </div>
        <div class="footer-contact-row">
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 010 1.18 2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92z"/></svg>
          +351 21 123 4567
        </div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    <span>&copy; 2026 SpotFinder. Todos os direitos reservados.</span>
    <div class="social-links">
      <a href="#"><svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg></a>
      <a href="#"><svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
      <a href="#"><svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z"/></svg></a>
    </div>
  </div>
</footer>

<script>
// NAV SCROLL
const nav = document.getElementById('mainNav');
window.addEventListener('scroll', () => { nav.classList.toggle('scrolled', window.scrollY > 60); });

// HERO PARALLAX + BG LOAD
window.addEventListener('load', () => { document.getElementById('heroBg').classList.add('loaded'); });

// TOAST
let toastTimer;
function showToast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 2800);
}

// FAVOURITES COM BASE DE DADOS
async function toggleFav(btn) {
  const card = btn.closest('.event-card');
  const eventId = card.dataset.id; // Agora ele já consegue ler o ID que adicionámos acima!

  try {
    const response = await fetch('processa_favorito.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'evento_id=' + eventId
    });
    
    const data = await response.json();

    if (data.status === 'sucesso') {
      btn.classList.toggle('liked');
      showToast(btn.classList.contains('liked') ? '❤️ Adicionado aos favoritos!' : 'Removido dos favoritos');
    } else if (data.status === 'nao_autorizado') {
      showToast('⚠️ Precisas de iniciar sessão para guardar favoritos!');
      setTimeout(() => window.location.href = 'spotfinder_auth.php', 2000);
    }
  } catch (error) {
    console.error('Erro na ligação:', error);
  }
}

// MODAL
function openModal(btn) {
  const card = btn.closest('.event-card');
  
  document.getElementById('modalTitle').textContent = card.dataset.name;
  document.getElementById('modalDate').textContent = card.dataset.date;
  document.getElementById('modalLocation').textContent = card.dataset.loc;
  document.getElementById('modalPrice').innerHTML = card.dataset.price + ' <small>por pessoa</small>';
  document.getElementById('modalBadge').textContent = card.dataset.cat;
  
  // Guardamos o ID do evento como atributo no próprio Modal para o botão de Compra o conseguir ler
  document.getElementById('modalOverlay').dataset.eventId = card.dataset.id;
  
  document.getElementById('modalOverlay').classList.add('open');
}

function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }

document.getElementById('modalOverlay').addEventListener('click', function(e) { if (e.target === this) closeModal(); });

// COMPRA DE BILHETE COM BASE DE DADOS
async function buyTicket() {
  const eventId = document.getElementById('modalOverlay').dataset.eventId;
  
  if (!eventId) return;

  const btnComprar = document.querySelector('.btn-buy');
  const textoOriginal = btnComprar.innerHTML;
  btnComprar.innerHTML = 'A processar...';

  try {
    const response = await fetch('processa_compra.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'evento_id=' + eventId
    });
    
    const data = await response.json();

    if (data.status === 'sucesso') {
      closeModal();
      showToast('✅ Compra confirmada! O teu bilhete foi emitido.');
    } else if (data.status === 'nao_autorizado') {
      closeModal();
      showToast('⚠️ Precisas de iniciar sessão para comprar bilhetes!');
      setTimeout(() => window.location.href = 'spotfinder_auth.php', 2000);
    }
  } catch (error) {
    console.error('Erro na ligação:', error);
    showToast('❌ Ocorreu um erro ao processar a compra.');
  } finally {
    btnComprar.innerHTML = textoOriginal;
  }
}

// FILTER TABS
let activeTab = 'todos';
function setTab(el, cat) {
  document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  activeTab = cat;
  applyFilters();
}

// CATEGORY CLICK
function filterByCategory(cat) {
  document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('active'));
  const el = document.querySelector('[data-cat="' + cat + '"]');
  if (el) el.classList.add('active');
  document.querySelector('#eventos').scrollIntoView({ behavior:'smooth' });
  document.querySelectorAll('.filter-tab').forEach(t => {
    t.classList.remove('active');
    const txt = t.textContent.trim();
    if (
      (cat==='Festival' && txt.includes('Festival')) ||
      (cat==='Concerto' && txt.includes('Concerto')) ||
      (cat==='Teatro' && txt.includes('Teatro')) ||
      (cat==='Gastronomia' && txt.includes('Gastro')) ||
      (cat==='Exposi\u00e7\u00e3o' && txt.includes('Exposi'))
    ) t.classList.add('active');
  });
  activeTab = cat;
  applyFilters();
}

// SEARCH
function quickSearch(q) {
  document.getElementById('searchQuery').value = q;
  document.querySelector('#eventos').scrollIntoView({ behavior:'smooth' });
  applyFilters();
}
function doSearch() {
  document.querySelector('#eventos').scrollIntoView({ behavior:'smooth' });
  applyFilters();
}
function filterEvents() { applyFilters(); }

function applyFilters() {
  const query = document.getElementById('searchQuery').value.toLowerCase();
  const location = document.getElementById('searchLocation').value.toLowerCase();
  const cards = document.querySelectorAll('.event-card');
  let visible = 0;
  cards.forEach(card => {
    const name = (card.dataset.name || '').toLowerCase();
    const cat  = (card.dataset.cat  || '');
    const loc  = (card.dataset.loc  || '').toLowerCase();
    const matchQ = !query    || name.includes(query)    || cat.toLowerCase().includes(query);
    const matchL = !location || loc.includes(location);
    const matchC = activeTab === 'todos' || cat === activeTab;
    if (matchQ && matchL && matchC) { card.classList.remove('search-hidden'); visible++; }
    else card.classList.add('search-hidden');
  });
  document.getElementById('noResults').classList.toggle('visible', visible === 0);
  const featured = document.querySelectorAll('#featuredRow .event-card');
  document.getElementById('featuredRow').style.display =
    [...featured].some(c => !c.classList.contains('search-hidden')) ? '' : 'none';
}

// ── LEAFLET MAP ──


const events = <?php echo json_encode($eventos_para_mapa); ?>;


// Mapeamento de cores premium para os pins de acordo com a categoria
const catColors = {
  Festival:  "#111111", // Black
  Concerto:  "#3b82f6", // Blue
  Exposição: "#a855f7", // Purple
  Teatro:    "#ef4444", // Red
};

// Gerador de Pins Customizados em SVG Puro com efeito Pulse/Glow
function makeIcon(cat) {
  const color = catColors[cat] || "#111";
  const svg = `<div class="custom-marker-wrapper">
    <div class="marker-pulse" style="background-color: ${color}"></div>
    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="46" viewBox="0 0 32 42">
      <path fill="${color}" stroke="#ffffff" stroke-width="1.5" d="M16 0C7.163 0 0 7.163 0 16c0 10 16 26 16 26S32 26 32 16C32 7.163 24.837 0 16 0z"/>
      <circle fill="white" cx="16" cy="16" r="6"/>
    </svg>
  </div>`;
  
  return L.divIcon({
    html: svg,
    className: 'custom-leaflet-marker',
    iconSize: [36, 46],
    iconAnchor: [18, 46],
    popupAnchor: [0, -48],
  });
}

// Inicializar mapa focado no centro de Portugal
const map = L.map('map', {
  center: [39.6, -8.2],
  zoom: 6.8,
  zoomControl: false, // Desativado para mover o controlador para uma posição melhor
  scrollWheelZoom: true,
});

// Adicionar controlador de Zoom no canto inferior direito para um UI mais limpo
L.control.zoom({ position: 'bottomright' }).addTo(map);

// LAYER MODERNO: Camada de mapa estilo Dark/Muted (CartoDB DarkMatter)
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
  subdomains: 'abcd',
  maxZoom: 20
}).addTo(map);

// Renderização dos Pins e das janelas de informação interativas (Popups)
events.forEach(ev => {
  const marker = L.marker([ev.lat, ev.lng], { icon: makeIcon(ev.cat) }).addTo(map);
  
  // Construção do layout HTML do mini-card dentro do mapa
  const popupContent = `
    <div class="map-popup-card">
      <div class="popup-img-header">
        <img src="${ev.img}" alt="${ev.name}">
        <span class="popup-cat-badge" style="background:${catColors[ev.cat] || '#111'}">${ev.cat}</span>
      </div>
      <div class="popup-card-body">
        <h4 class="popup-card-title">${ev.name}</h4>
        <div class="popup-card-meta">
          <p><span>📅</span> ${ev.date}</p>
          <p><span>📍</span> ${ev.loc}</p>
        </div>
        <div class="popup-card-footer">
          <div class="popup-card-price">
            <small>Bilhetes</small>
            <div>${ev.price === 'Gratuito' ? 'Gratuito' : '&euro;' + ev.price}</div>
          </div>
          <!-- Botão interativo que simula a abertura do modal principal de detalhes -->
          <button class="popup-card-btn" onclick="openModalFromMap('${ev.id}')">
            Ver Spot &rarr;
          </button>
        </div>
      </div>
    </div>
  `;
  
  marker.bindPopup(popupContent, {
    maxWidth: 290,
    minWidth: 260,
    className: 'modern-popup-wrapper'
  });
});

// Função de ponte para conectar o clique do mapa ao Modal Global existente no seu projeto
function openModalFromMap(eventId) {
  const evData = events.find(e => e.id === eventId);
  if (!evData) return;
  
  // Preenche o modal padrão do seu site usando os dados capturados do mapa
  document.getElementById('modalTitle').textContent = evData.name;
  document.getElementById('modalDate').textContent = evData.date;
  document.getElementById('modalLocation').textContent = evData.loc;
  document.getElementById('modalPrice').innerHTML = (evData.price === 'Gratuito' ? 'Gratuito' : '&euro;' + evData.price) + ' <small>por pessoa</small>';
  document.getElementById('modalBadge').textContent = evData.cat;
  
  // Atualiza a cor de fundo do badge do modal para condizer
  document.getElementById('modalBadge').style.background = catColors[evData.cat] || '#111';
  
  // Abre o modal
  document.getElementById('modalOverlay').classList.add('open');
}


// ABRIR E FECHAR O MENU MOBILE
function toggleMobileMenu() {
  document.getElementById('mobileMenu').classList.toggle('open');
}

</script>
</body>
</html>