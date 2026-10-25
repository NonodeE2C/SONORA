
<?php
require_once 'functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$stmt = $pdo->prepare('SELECT * FROM albums WHERE id = ?');
$stmt->execute([$id ?: 0]);
$album = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$album) {
    http_response_code(404);
    page_header('Album introuvable');
    echo '<h1>Album introuvable</h1>';
    echo '<a href="artistes.php">Retour aux artistes</a>';
    page_footer();
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, title, track_number, themes
     FROM tracks
     WHERE album_id = ?
     ORDER BY track_number ASC, title ASC'
);
$stmt->execute([$album['id']]);
$tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);

page_header($album['title']);
?>

<section class="album-page">
    <div class="album-detail">
        <?php if (!empty($album['cover_image'])): ?>
            <img
                class="album-cover-large"
                src="<?= e($album['cover_image']) ?>"
                alt="Pochette de <?= e($album['title']) ?>"
            >
        <?php endif; ?>

        <div>
            <p class="eyebrow">ALBUM</p>
            <h1><?= e($album['title']) ?></h1>

            <p><?= e($album['artist_name']) ?></p>

            <?php if (!empty($album['release_year'])): ?>
                <p><?= (int) $album['release_year'] ?></p>
            <?php endif; ?>
        </div>
    </div>

    <section class="album-tracks">
        <h2>Les titres de l'album</h2>

        <?php if ($tracks): ?>
            <div class="track-list">
                <?php foreach ($tracks as $track): ?>
                    <a
                        class="track-item"
                        href="chanson.php?id=<?= (int) $track['id'] ?>"
                    >
                        <span>
                            <?= $track['track_number'] !== null
                                ? (int) $track['track_number'] . '. '
                                : '' ?>
                            <?= e($track['title']) ?>
                        </span>
                        <span>Découvrir le sens des paroles ↗</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Les titres de cet album seront bientôt disponibles.</p>
        <?php endif; ?>
    </section>
</section>

<?php page_footer(); ?>
