<?php
require_once 'functions.php';
$artists = $pdo->query('SELECT * FROM artists ORDER BY name LIMIT 6')->fetchAll();
$news = $pdo->query('SELECT n.*, a.name AS artist_name FROM news n LEFT JOIN artists a ON a.id=n.artist_id ORDER BY n.published_at DESC LIMIT 5')->fetchAll();
page_header('Accueil');
?>
<section class="hero">
  <div class="eyebrow">LA MUSIQUE NOUS RASSEMBLE</div>
  <h1>Tous tes artistes.<br><span>Toute leur actualité.</span></h1>
  <p>Découvre les nouvelles, retrouve les fans et partage ce qui te fait vibrer.</p>
  <div class="actions"><a class="button" href="artistes.php">Découvrir les artistes</a><a class="button secondary" href="communaute.php">Rejoindre la communauté</a></div>
</section>
<section class="section-head">
  <div>
    <div class="eyebrow">À SUIVRE</div>
    <h2>Artistes en vedette</h2>
  </div>
  <a href="artistes.php">Tout voir →</a>
</section>
<div class="artist-grid">
<?php foreach ($artists as $a): ?>
  <a class="artist-card" style="--accent:<?= e($a['accent_color']) ?>" href="artiste.php?id=<?= (int)$a['id'] ?>">
<div class="artist-art">
    <div class="artist-photo">
     <img src="/images/bigflo.jpg" alt="<?= e($a['name']) ?>">
    </div>
</div>
<div class="artist-card-body">
  <h3><?= e($a['name']) ?></h3>
  <p><?= e($a['genre']) ?></p>
</div>
</a><?php endforeach; ?>
</div>
<section class="section-head">
<div>
  <div class="eyebrow">LE FIL MUSICAL</div>
  <h2>Dernières actualités</h2>
</div>
<a href="actualites.php">Toutes les actualités →</a></section>
<div class="news-list">
<?php foreach ($news as $n): ?><article class="news-card"><div class="news-meta"><?= e($n['artist_name'] ?? 'Musique') ?> · <?= e(date('d/m/Y', strtotime($n['published_at']))) ?></div><h3><?= e($n['title']) ?></h3><p><?= e($n['summary']) ?></p><?php if ($n['source_url']): ?><a target="_blank" rel="noopener noreferrer" href="<?= e($n['source_url']) ?>">Lire la source ↗</a><?php endif; ?></article><?php endforeach; ?>
<?php if (!$news): ?><p>Aucune actualité pour le moment.</p><?php endif; ?>
</div>
<?php page_footer(); ?>
