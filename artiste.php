<?php
require_once 'functions.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$stmt = $pdo->prepare('SELECT * FROM artists WHERE id=?'); $stmt->execute([$id ?: 0]); $artist = $stmt->fetch();
if (!$artist) { http_response_code(404); page_header('Artiste introuvable'); echo '<h1>Artiste introuvable</h1><a href="artistes.php">Retour aux artistes</a>'; page_footer(); exit; }
if ($_SERVER['REQUEST_METHOD']==='POST') {
    require_login();
    $stmt = $pdo->prepare('INSERT IGNORE INTO follows(user_id,artist_id) VALUES(?,?)');
    $stmt->execute([$_SESSION['user_id'], $artist['id']]);
    header('Location: artiste.php?id='.(int)$artist['id']); exit;
}
$stmt = $pdo->prepare('SELECT n.* FROM news n WHERE n.artist_id=? ORDER BY n.published_at DESC'); 
$stmt->execute([$artist['id']]); $news=$stmt->fetchAll();
$stmt = $pdo->prepare('SELECT id,name FROM chat_rooms WHERE artist_id=? LIMIT 1'); 
$stmt->execute([$artist['id']]); $room=$stmt->fetch();
$isFollowing = false;
if (logged_in()) { $stmt=$pdo->prepare('SELECT 1 FROM follows WHERE user_id=? AND artist_id=?'); 
$stmt->execute([$_SESSION['user_id'],$artist['id']]); $isFollowing=(bool)$stmt->fetchColumn(); }
page_header($artist['name']);

?>



   


<section class="artist-hero">

    <?php if (!empty($artist['image'])): ?>
        <img
            src="<?= e($artist['image']) ?>"
            alt="<?= e($artist['name']) ?>"
            class="artist-hero-image"
        >
    <?php endif; ?>

    <div class="artist-hero-content">


    <div class="artist-hero-content">

        <div class="eyebrow">ESPACE ARTISTE</div>

        <h1><?= e($artist['name']) ?></h1>

        <p><?= e($artist['genre']) ?></p>

        <p><?= e($artist['bio']) ?></p>

        <?php if (logged_in()): ?>
            <form method="post">
                <button class="button" type="submit">
                    <?= $isFollowing ? 'Suivre à nouveau' : '＋ Suivre cet artiste' ?>
                </button>
            </form>
        <?php else: ?>
            <a class="button" href="connexion.php">
                Connecte-toi pour suivre
            </a>
        <?php endif; ?>

        <?php if (!empty($artist['official_url'])): ?>
            <p>
                <a
                    target="_blank"
                    rel="noopener noreferrer"
                    href="<?= e($artist['official_url']) ?>"
                >
                    Site officiel ↗
                </a>
            </p>
        <?php endif; ?>

    </div>
</section>



<?php

$stmt = $pdo->prepare(
    'SELECT id, title, release_year, cover_image
     FROM albums
     WHERE artist_id = ?
     ORDER BY release_year ASC, title ASC'
);
$stmt->execute([$artist['id']]);
$albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<section class="artist-albums">
    <div class="section-head">
        <div>
            <div class="eyebrow">LEUR MUSIQUE</div>
            <h2>Discographie</h2>
        </div>
    </div>

    <div class="albums-grid">
        <?php if ($albums): ?>
            <?php foreach ($albums as $album): ?>
                
<a class="album-card" href="album.php?id=<?= (int) ($album['id'] ?? 0) ?>">
    <?php if (!empty($album['cover_image'])): ?>
        <img
            src="<?= e($album['cover_image']) ?>"
            alt="Pochette de <?= e($album['title']) ?>"
        >
    <?php endif; ?>

    <h3><?= e($album['title']) ?></h3>

    <?php if (!empty($album['release_year'])): ?>
        <p><?= (int) $album['release_year'] ?></p>
    <?php endif; ?>
</a>

            <?php endforeach; ?>
        <?php else: ?>
            <p>Les albums de cet artiste seront bientôt disponibles.</p>
        <?php endif; ?>
    </div>
</section>


<section class="section-head">
    <div>
        <div class="eyebrow">À LA UNE</div>
        <h2>Actualités</h2>
    </div>
</section>
<div class="news-list">
    <?php foreach ($news as $n): ?>
        <article class="news-card">
            <h3><?= e($n['title']) ?></h3>
            <p><?= e($n['summary']) ?></p>
            <?php if ($n['source_url']): ?>
            <a target="_blank" rel="noopener noreferrer" href="<?= e($n['source_url']) ?>">Lire la source ↗</a>
            <?php endif; ?></article><?php endforeach; ?><?php if (!$news): ?>
            <p>Les actualités de cet artiste apparaîtront ici.</p><?php endif; ?>
</div>
<?php if ($room): ?><p><a class="button secondary" href="chat.php?room=<?= (int)$room['id'] ?>">Rejoindre <?= e($room['name']) ?></a></p><?php endif; ?>
<?php page_footer(); ?>