
<?php
require_once 'functions.php';

require_login();

/*
|--------------------------------------------------------------------------
| SONORA STORIES : CREATION D'UNE HISTOIRE
|--------------------------------------------------------------------------
*/

$error = [];
$success = '';

$title = '';
$description = '';
$genre = 'Romance';
$story_type = 'original';
$artist_name = '';
$cover_image = '';

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

/*
|--------------------------------------------------------------------------
| TRAITEMENT DU FORMULAIRE
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupérer et nettoyer les données
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $genre = trim($_POST['genre'] ?? 'Autre');
    $story_type = $_POST['story_type'] ?? 'original';
    $artist_name = trim($_POST['artist_name'] ?? '');
    $cover_image = trim($_POST['cover_image'] ?? '');

    // Vérifier le titre
    if ($title === '') {
        $error[] = 'Le titre de ton histoire est obligatoire.';
    } elseif (mb_strlen($title) > 255) {
        $error[] = 'Le titre ne doit pas dépasser 255 caractères.';
    }

    // Vérifier le résumé
    if ($description === '') {
        $error[] = 'Le résumé est obligatoire.';
    }

    // Vérifier le genre
    if (!in_array($genre, $genres, true)) {
        $error[] = 'Le genre sélectionné est invalide.';
    }

    // Vérifier le type d'histoire
    if (!in_array($story_type, ['original', 'fanfiction'], true)) {
        $error[] = 'Le type d’histoire sélectionné est invalide.';
    }

    // Une fanfiction doit préciser l'artiste ou l'univers concerné
    if ($story_type === 'fanfiction' && $artist_name === '') {
        $error[] = 'Précise l’artiste ou l’univers musical de ta fanfiction.';
    }

    // Vérifier l'adresse de la couverture
    if ($cover_image !== '' &&
        (!filter_var($cover_image, FILTER_VALIDATE_URL) ||
         !in_array(strtolower((string) parse_url($cover_image, PHP_URL_SCHEME)),
             ['http', 'https'], true))) {
        $error[] = 'La couverture doit être une adresse web valide en HTTPS ou HTTP.';
    }

    /*
    |--------------------------------------------------------------------------
    | ENREGISTRER L'HISTOIRE
    |--------------------------------------------------------------------------
    */

    if (empty($error)) {

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO stories
                (
                    author_id,
                    title,
                    description,
                    cover_image,
                    genre,
                    story_type,
                    artist_name,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $_SESSION['user_id'],
                $title,
                $description,
                $cover_image !== '' ? $cover_image : null,
                $genre,
                $story_type,
                $story_type === 'fanfiction' ? $artist_name : null,
                'draft'
            ]);

            $story_id = (int) $pdo->lastInsertId();

            // Rediriger vers l'éditeur du premier chapitre
            header('Location: write_chapter.php?story_id=' . $story_id);
            exit;

        } catch (PDOException $exception) {
            $error[] = 'Impossible d’enregistrer ton histoire pour le moment.';
        }
    }
}

page_header('Créer une histoire');
?>

<main class="story-editor">

    <!-- EN-TÊTE -->

    <header class="editor-heading">
        <p class="eyebrow">SONORA STORIES</p>
        <h1>Crée ton propre univers ✨</h1>
        <p>
            Une idée, un artiste, une émotion…
            C’est ici que ton histoire commence.
        </p>
    </header>

    <!-- AFFICHAGE DES ERREURS -->

    <?php if (!empty($error)): ?>
        <div class="editor-errors" role="alert">
            <h2>Quelques petites choses à corriger</h2>
            <ul>
                <?php foreach ($error as $message): ?>
                    <li><?= e($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- FORMULAIRE PRINCIPAL -->

    <form method="post" class="story-form">

        <!-- INFORMATIONS PRINCIPALES -->

        <section class="editor-panel">
            <div class="editor-section-heading">
                <span class="editor-number">01</span>
                <div>
                    <h2>Les bases de ton histoire</h2>
                    <p>Donne un nom et une identité à ton récit.</p>
                </div>
            </div>

            <label for="title">Titre de l'histoire *</label>
            <input
                type="text"
                id="title"
                name="title"
                maxlength="255"
                required
                value="<?= e($title) ?>"
                placeholder="Ex. : Les mélodies du destin"
            >

            <label for="description">Résumé de l'histoire *</label>
            <textarea
                id="description"
                name="description"
                rows="6"
                required
                placeholder="De quoi parle ton histoire ? Qui sont les personnages ? Quel est le problème principal ?"
            ><?= e($description) ?></textarea>

            <p class="editor-help">
                Astuce : présente le personnage principal, le contexte
                et ce qui donnera envie aux lecteurs de découvrir la suite.
            </p>
        </section>

        <!-- TYPE D'HISTOIRE -->

        <section class="editor-panel">
            <div class="editor-section-heading">
                <span class="editor-number">02</span>
                <div>
                    <h2>Quel genre d'histoire écris-tu ?</h2>
                    <p>Choisis l'univers dans lequel tu veux raconter ton récit.</p>
                </div>
            </div>

            <div class="story-type-options">

                <label class="story-type-option">
                    <input
                        type="radio"
                        name="story_type"
                        value="original"
                        <?= $story_type === 'original' ? 'checked' : '' ?>
                    >
                    <span class="story-type-icon">📖</span>
                    <span>
                        <strong>Histoire originale</strong>
                        <small>
                            Tes personnages, ton univers, tes propres idées.
                        </small>
                    </span>
                </label>

                <label class="story-type-option">
                    <input
                        type="radio"
                        name="story_type"
                        value="fanfiction"
                        <?= $story_type === 'fanfiction' ? 'checked' : '' ?>
                    >
                    <span class="story-type-icon">🎤</span>
                    <span>
                        <strong>Fanfiction musicale</strong>
                        <small>
                            Une fiction inspirée d'un artiste ou d'un univers musical.
                        </small>
                    </span>
                </label>

            </div>

            <label for="artist_name">Artiste ou univers musical</label>
            <input
                type="text"
                id="artist_name"
                name="artist_name"
                maxlength="255"
                value="<?= e($artist_name) ?>"
                placeholder="Ex. : Bigflo & Oli"
            >

            <p class="editor-help">
                Ce champ est utile pour les fanfictions.
                Pour une histoire originale, tu peux le laisser vide.
            </p>
        </section>

        <!-- GENRE LITTERAIRE -->

        <section class="editor-panel">
            <div class="editor-section-heading">
                <span class="editor-number">03</span>
                <div>
                    <h2>La catégorie</h2>
                    <p>Aide les lecteurs à trouver ton histoire.</p>
                </div>
            </div>

            <label for="genre">Genre littéraire</label>
            <select id="genre" name="genre">
                <?php foreach ($genres as $g): ?>
                    <option
                        value="<?= e($g) ?>"
                        <?= $genre === $g ? 'selected' : '' ?>
                    >
                        <?= e($g) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </section>

        <!-- COUVERTURE -->

        <section class="editor-panel">
            <div class="editor-section-heading">
                <span class="editor-number">04</span>
                <div>
                    <h2>La couverture</h2>
                    <p>Choisis une image qui représente ton histoire.</p>
                </div>
            </div>

            <label for="cover_image">Adresse de l'image</label>
            <input
                type="url"
                id="cover_image"
                name="cover_image"
                value="<?= e($cover_image) ?>"
                placeholder="https://exemple.fr/ma-couverture.jpg"
            >

            <p class="editor-help">
                Pour cette première version, utilise une adresse d'image
                accessible sur le Web. Nous pourrons ensuite ajouter
                un véritable bouton d'importation depuis ton ordinateur.
            </p>
        </section>

        <!-- BOUTONS -->

        <div class="editor-actions">
            <a href="stories.php" class="button secondary">
                Annuler
            </a>

            <button type="submit" class="button">
                Créer mon histoire et continuer ✍️
            </button>
        </div>

        <p class="editor-footer-note">
            Ton histoire sera enregistrée en brouillon.
            Elle ne sera pas visible dans le catalogue public tant que tu ne l'auras pas publiée.
        </p>

    </form>
</main>

<?php page_footer(); ?>
