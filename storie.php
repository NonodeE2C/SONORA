
<?php
require_once 'functions.php';

/*
|--------------------------------------------------------------------------
| SONORA STORIES
|--------------------------------------------------------------------------
*/

$search = trim($_GET['q'] ?? '');
$genre = trim($_GET['genre'] ?? '');
$type = trim($_GET['type'] ?? '');

$genres = [
    'Romance',
    'Amitié',
    'Drame',
    'Comédie',
    'Fantastique',
    'Science-fiction',
    'Mystère',
    'Musique',
    'Tranche de vie',
    'Autre'
];

$types = ['original', 'fanfiction'];

if (!in_array($genre, $genres, true)) {
    $genre = '';
}

if (!in_array($type, $types, true)) {
    $type = '';
}

/*
|--------------------------------------------------------------------------
| RECHERCHE DES HISTOIRES PUBLIEES
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        s.id,
        s.title,
        s.description,
        s.cover_image,
        s.genre,
        s.story_type,
        s.artist_name,
        s.created_at,
        u.username
    FROM stories s
    JOIN users u ON u.id = s.author_id
    WHERE s.status = 'published'
";

$params = [];

if ($search !== '') {
    $sql .= " AND (
        s.title LIKE ?
        OR s.description LIKE ?
        OR s.artist_name LIKE ?
        OR u.username LIKE ?
    )";

    $term = '%' . $search . '%';

    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($genre !== '') {
    $sql .= " AND s.genre = ?";
    $params[] = $genre;
}

if ($type !== '') {
    $sql .= " AND s.story_type = ?";
    $params[] = $type;
}

$sql .= " ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

page_header('Sonora Stories');
?>

<main class="sonora-stories">

    <!-- BANNIERE PRINCIPALE -->

    <section class="stories-hero">
        <div class="stories-hero-content">

            <span class="stories-badge">
                📚 TON UNIVERS, TES HISTOIRES
            </span>

            <h1>
                La musique inspire.
                <span>Ton imagination raconte.</span>
            </h1>

            <p class="stories-hero-description">
                Entre dans des univers imaginaires, découvre des personnages,
                vis des aventures et partage tes propres histoires avec
                la communauté Sonora.
            </p>

            <div class="stories-hero-actions">
                <a href="write.php" class="button">
                    ✍️ Créer une histoire
                </a>

                <a href="#bibliotheque" class="button secondary">
                    📖 Explorer les histoires
                </a>
            </div>

            <div class="stories-hero-details">
                <span>🎤 Fanfictions musicales</span>
                <span>✨ Histoires originales</span>
                <span>💜 Communauté de lecteurs</span>
            </div>

        </div>
    </section>

    <!-- INTRODUCTION -->

    <section class="stories-introduction">
        <div>
            <p class="eyebrow">BIENVENUE DANS TON IMAGINAIRE</p>
            <h2>Chaque histoire ouvre une porte.</h2>
            <p>
                Une rencontre inattendue, une histoire d'amitié,
                une aventure musicale ou un univers entièrement inventé :
                trouve ton prochain récit préféré.
            </p>
        </div>

        <a href="write.php" class="stories-write-link">
            Tu as une idée ?
            <strong>Écris-la sur Sonora ↗</strong>
        </a>
    </section>

    <!-- RECHERCHE ET FILTRES -->

    <section class="stories-library" id="bibliotheque">

        <div class="stories-library-heading">
            <div>
                <p class="eyebrow">LA BIBLIOTHÈQUE</p>
                <h2>Les histoires à découvrir</h2>
                <p>Recherche un récit selon tes envies.</p>
            </div>

            <span class="stories-count">
                <?= count($stories) ?>
                <?= count($stories) > 1 ? 'histoires' : 'histoire' ?>
            </span>
        </div>

        <form method="get" action="stories.php" class="stories-filters">

            <div class="stories-search">
                <label for="q">Rechercher</label>
                <input
                    type="search"
                    id="q"
                    name="q"
                    value="<?= e($search) ?>"
                    placeholder="Un titre, un artiste, un auteur..."
                >
            </div>

            <div>
                <label for="genre">Genre</label>
                <select id="genre" name="genre">
                    <option value="">Tous les genres</option>

                    <?php foreach ($genres as $g): ?>
                        <option
                            value="<?= e($g) ?>"
                            <?= $genre === $g ? 'selected' : '' ?>
                        >
                            <?= e($g) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="type">Type d'histoire</label>
                <select id="type" name="type">
                    <option value="">Toutes les histoires</option>
                    <option
                        value="fanfiction"
                        <?= $type === 'fanfiction' ? 'selected' : '' ?>
                    >
                        Fanfictions musicales
                    </option>
                    <option
                        value="original"
                        <?= $type === 'original' ? 'selected' : '' ?>
                    >
                        Histoires originales
                    </option>
                </select>
            </div>

            <div class="stories-filter-actions">
                <button type="submit" class="button">
                    Rechercher
                </button>

                <a href="stories.php" class="stories-reset">
                    Réinitialiser
                </a>
            </div>

        </form>

        <!-- CARTES DES HISTOIRES -->

        <?php if ($stories): ?>

            <div class="stories-grid">

                <?php foreach ($stories as $story): ?>

                    <article class="story-card">

                        <a
                            href="story.php?id=<?= (int) $story['id'] ?>"
                            class="story-cover-link"
                            aria-label="Lire <?= e($story['title']) ?>"
                        >
                            <?php if (!empty($story['cover_image'])): ?>

                                <img
                                    src="<?= e($story['cover_image']) ?>"
                                    alt="Couverture de <?= e($story['title']) ?>"
                                    class="story-cover"
                                    loading="lazy"
                                >

                            <?php else: ?>

                                <div class="story-cover-placeholder">
                                    <span>📖</span>
                                    <strong><?= e($story['title']) ?></strong>
                                    <small>SONORA STORIES</small>
                                </div>

                            <?php endif; ?>
                        </a>

                        <div class="story-card-content">

                            <div class="story-card-tags">
                                <span class="story-tag">
                                    <?= e($story['genre']) ?>
                                </span>

                                <?php if ($story['story_type'] === 'fanfiction'): ?>
                                    <span class="story-tag story-tag-music">
                                        🎤 Fanfiction
                                    </span>
                                <?php else: ?>
                                    <span class="story-tag story-tag-original">
                                        ✨ Originale
                                    </span>
                                <?php endif; ?>
                            </div>

                            <h3>
                                <a href="story.php?id=<?= (int) $story['id'] ?>">
                                    <?= e($story['title']) ?>
                                </a>
                            </h3>

                            <?php if (
                                $story['story_type'] === 'fanfiction'
                                && !empty($story['artist_name'])
                            ): ?>
                                <p class="story-artist">
                                    Inspirée de <?= e($story['artist_name']) ?>
                                </p>
                            <?php endif; ?>

                            <p class="story-description">
                                <?= e($story['description']) ?>
                            </p>

                            <div class="story-card-footer">
                                <span>
                                    Par <?= e($story['username']) ?>
                                </span>

                                <a
                                    href="story.php?id=<?= (int) $story['id'] ?>"
                                    class="story-read-link"
                                >
                                    Lire →
                                </a>
                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="stories-empty">

                <span class="stories-empty-icon">📚</span>

                <?php if (
                    $search !== ''
                    || $genre !== ''
                    || $type !== ''
                ): ?>

                    <h3>Aucune histoire trouvée</h3>

                    <p>
                        Essaie un autre titre, un autre auteur
                        ou une autre catégorie.
                    </p>

                    <a href="stories.php" class="button secondary">
                        Voir toutes les histoires
                    </a>

                <?php else: ?>

                    <h3>La bibliothèque attend ses premiers récits.</h3>

                    <p>
                        Aucun récit n'est encore publié.
                        Tu peux être parmi les premiers auteurs de Sonora Stories !
                    </p>

                    <a href="write.php" class="button">
                        ✍️ Créer la première histoire
                    </a>

                <?php endif; ?>

            </div>

        <?php endif; ?>

    </section>

    <!-- INVITATION A ECRIRE -->

    <section class="stories-cta">

        <div>
            <p class="eyebrow">À TON TOUR</p>

            <h2>Et si la prochaine histoire était la tienne ?</h2>

            <p>
                Imagine un univers, invente des personnages,
                écris ton premier chapitre et partage ton récit
                avec les lecteurs de Sonora.
            </p>
        </div>

        <a href="write.php" class="button">
            Commencer à écrire ✍️
        </a>

    </section>

</main>

<?php page_footer(); ?>
