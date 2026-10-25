<?php
require_once 'functions.php';
$news = $pdo->query('SELECT n.*, a.name AS artist_name FROM news n LEFT JOIN artists a ON a.id=n.artist_id ORDER BY n.published_at DESC')->fetchAll();
page_header('Actualités');
?>
<div class="eyebrow">LE FIL MUSICAL</div>
<h1>Actualités</h1>
<p>Les annonces publiées sur Sonora. Les exemples doivent être remplacés par des actualités vérifiées.</p>
<div class="news-list">
<?php foreach ($news as $n): ?>
<article class="news-card">
<div class="news-meta">
<?= e($n['artist_name'] ?? 'Musique') ?> 
<?= e(date('d/m/Y', strtotime($n['published_at']))) ?>
</div>
    <h3><?= e($n['title']) ?></h3>
    <p><?= e($n['summary']) ?></p>
    <small>Source : <?= e($n['source_name']) ?>
    </small>
    <?php if ($n['source_url']): ?>
    <p>
        <a target="_blank" rel="noopener noreferrer" href="<?= e($n['source_url']) ?>">Lire la source ↗</a>
    </p>
    <?php endif; ?></article><?php endforeach; ?>
</div>
<?php page_footer(); ?>
