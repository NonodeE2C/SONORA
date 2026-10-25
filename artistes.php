<?php
require_once 'functions.php';
$q = trim($_GET['q'] ?? '');
$stmt = $pdo->prepare('SELECT * FROM artists WHERE name LIKE ? OR genre LIKE ? ORDER BY name');
$stmt->execute(['%'.$q.'%','%'.$q.'%']); $artists = $stmt->fetchAll();
page_header('Artistes');
?>
<div class="eyebrow">EXPLORE LE MONDE MUSICAL</div>
<h1>Les artistes</h1>
<p>Retrouve les pages de tes artistes préférés.</p>
<form class="search-form" method="get">
    <input name="q" value="<?= e($q) ?>" placeholder="Rechercher un artiste ou un style…">
    <button class="button" type="submit">Rechercher</button>
</form>
<div class="artist-grid"><?php foreach ($artists as $a): ?><a class="artist-card" style="--accent:<?= e($a['accent_color']) ?>" href="artiste.php?id=<?= (int)$a['id'] ?>">
        <div class="artist-art">
            <?php if (!empty($a['image'])): ?>
        <img
        src="<?= htmlspecialchars($a['image'], ENT_QUOTES, 'UTF-8') ?>"
        class="artist-photo"
        >
        <?php endif; ?>
        </div>
        <div class="artist-card-body">
            <h3><?= e($a['name']) ?></h3>
            <p><?= e($a['genre']) ?></p>
        </div>
    </a><?php endforeach; ?></div>
<?php if (!$artists): ?><p>Aucun artiste trouvé. Essaie un autre terme.</p><?php endif; ?>
<?php page_footer(); ?>
