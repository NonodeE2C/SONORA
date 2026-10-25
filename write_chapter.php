
<?php

// =====================================================
// 1. INITIALISATION
// =====================================================

// Charge les fonctions communes de Sonora :
// connexion à la base de données, affichage du site, etc.
require_once 'functions.php';

// Oblige le visiteur à être connecté.
require_login();

// Tableau qui contiendra les messages d'erreur.
$errors = [];

// Valeurs initiales du formulaire.
$title = '';
$content = '';

// Vérifie si le formulaire a été envoyé.
$is_post = ($_SERVER['REQUEST_METHOD'] === 'POST');


// =====================================================
// 2. RÉCUPÉRATION DE L'IDENTIFIANT DE L'HISTOIRE
// =====================================================

// Lors de l'ouverture de la page, l'identifiant vient de l'URL.
// Exemple : write_chapter.php?story_id=12
//
// Lors de l'envoi du formulaire, il vient du champ caché.
if ($is_post) {
    $story_id = filter_input(
        INPUT_POST,
        'story_id',
        FILTER_VALIDATE_INT
    );
} else {
    $story_id = filter_input(
        INPUT_GET,
        'story_id',
        FILTER_VALIDATE_INT
    );
}

// On refuse les identifiants invalides ou inférieurs à 1.
if (!$story_id || $story_id < 1) {
    http_response_code(400);
    exit('Erreur : identifiant de l’histoire invalide.');
}


// =====================================================
// 3. RÉCUPÉRATION DE L'HISTOIRE
// =====================================================

// On recherche l'histoire dans la base de données.
$query = $pdo->prepare(
    'SELECT id, title, author_id, status
     FROM stories
     WHERE id = ?'
);

$query->execute([$story_id]);

$story = $query->fetch(PDO::FETCH_ASSOC);

// Si l'histoire n'existe pas, on arrête le traitement.
if (!$story) {
    http_response_code(404);
    exit('Erreur : cette histoire est introuvable.');
}


// =====================================================
// 4. VÉRIFICATION DU PROPRIÉTAIRE
// =====================================================

// On compare l'auteur de l'histoire avec l'utilisateur connecté.
// Cela empêche un autre membre d'ajouter un chapitre à sa place.
$current_user_id = (int) $_SESSION['user_id'];
$author_id = (int) $story['author_id'];

if ($author_id !== $current_user_id) {
    http_response_code(403);
    exit('Accès refusé : tu ne possèdes pas cette histoire.');
}


// =====================================================
// 5. RÉCUPÉRATION DES DONNÉES DU FORMULAIRE
// =====================================================

if ($is_post) {

    // trim() retire les espaces au début et à la fin.
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

}


// =====================================================
// 6. VALIDATION DES DONNÉES
// =====================================================

if ($is_post) {

    // Vérification du titre.
    if ($title === '') {
        $errors[] = 'Le titre du chapitre est obligatoire.';
    }

    // Vérification de la longueur du titre.
    if (mb_strlen($title) > 255) {
        $errors[] = 'Le titre ne peut pas dépasser 255 caractères.';
    }

    // Vérification du contenu.
    if ($content === '') {
        $errors[] = 'Tu dois écrire du contenu avant d’enregistrer.';
    }

    // Limite de sécurité raisonnable pour un chapitre.
    if (mb_strlen($content) > 100000) {
        $errors[] = 'Le chapitre est trop long. Réduis son contenu.';
    }

}


// =====================================================
// 7. ENREGISTREMENT DU CHAPITRE
// =====================================================

if ($is_post && empty($errors)) {

    try {

        // On récupère le plus grand numéro de chapitre existant.
        // Si aucun chapitre n'existe, on commence à 1.
        $query = $pdo->prepare(
            'SELECT COALESCE(MAX(chapter_number), 0) + 1
             FROM chapters
             WHERE story_id = ?'
        );

        $query->execute([$story_id]);

        $chapter_number = (int) $query->fetchColumn();


        // On insère le nouveau chapitre dans la base.
        // Son statut est "draft", donc il reste en brouillon.
        $query = $pdo->prepare(
            'INSERT INTO chapters
                (
                    story_id,
                    title,
                    content,
                    chapter_number,
                    status
                )
             VALUES (?, ?, ?, ?, ?)'
        );

        $query->execute([
            $story_id,
            $title,
            $content,
            $chapter_number,
            'draft'
        ]);


        // Récupération de l'identifiant du chapitre créé.
        $chapter_id = (int) $pdo->lastInsertId();


        // Redirection vers la page du chapitre créé.
        // Nous créerons cette page dans une prochaine étape.
        header(
            'Location: edit_chapter.php?id=' . $chapter_id
        );

        exit;

    } catch (PDOException $exception) {

        // On ne montre pas les détails techniques de la base
        // de données au visiteur.
        error_log($exception->getMessage());

        $errors[] =
            'Une erreur est survenue pendant l’enregistrement. '
            . 'Vérifie ta connexion et réessaie.';

    }

}


// =====================================================
// 8. PRÉPARATION DU NOMBRE DE CHAPITRES
// =====================================================

// On compte les chapitres déjà créés pour cette histoire.
$query = $pdo->prepare(
    'SELECT COUNT(*)
     FROM chapters
     WHERE story_id = ?'
);

$query->execute([$story_id]);

$chapter_count = (int) $query->fetchColumn();

// Ce numéro est indicatif.
// Le numéro définitif sera recalculé au moment de l'insertion.
$next_chapter_number = $chapter_count + 1;


// =====================================================
// 9. AFFICHAGE DE LA PAGE
// =====================================================

page_header('Écrire un chapitre');

?>

<main class="chapter-editor-page">

    <!-- Présentation de l'éditeur -->
    <header class="chapter-editor-hero">

        <p class="chapter-editor-label">
            SONORA STORIES
        </p>

        <h1 class="chapter-editor-title">
            Écris ton prochain chapitre ✍️
        </h1>

        <p class="chapter-editor-subtitle">
            Chaque histoire commence par quelques mots.
            À toi de raconter la suite.
        </p>

    </header>


    <!-- Informations sur l'histoire en cours -->
    <section class="chapter-story-information">

        <div>
            <span class="chapter-small-label">
                HISTOIRE EN COURS
            </span>

            <h2><?= e($story['title']) ?></h2>

            <p>
                Chapitres déjà enregistrés :
                <strong><?= $chapter_count ?></strong>
            </p>
        </div>

        <div class="chapter-number-preview">
            <span>Prochain chapitre</span>

            <strong>
                <?= $next_chapter_number ?>
            </strong>
        </div>

    </section>


    <!-- Affichage des erreurs -->
    <?php if (!empty($errors)): ?>

        <div class="chapter-errors" role="alert">

            <h2>Quelques points à corriger</h2>

            <ul>
                <?php foreach ($errors as $error): ?>

                    <li><?= e($error) ?></li>

                <?php endforeach; ?>
            </ul>

        </div>

    <?php endif; ?>


    <!-- Formulaire principal -->
    <section class="chapter-editor-card">

        <form
            method="POST"
            action="write_chapter.php?story_id=<?= $story_id ?>"
            id="chapter-form"
        >

            <!-- L'identifiant est transmis sans être affiché -->
            <input
                type="hidden"
                name="story_id"
                value="<?= $story_id ?>"
            >


            <!-- Titre du chapitre -->
            <div class="chapter-form-group">

                <label for="chapter-title">
                    Titre du chapitre
                </label>

                <input
                    type="text"
                    id="chapter-title"
                    name="title"
                    maxlength="255"
                    placeholder="Ex. : Chapitre 1, La première rencontre..."
                    value="<?= e($title) ?>"
                    required
                >

                <small>
                    Choisis un titre qui donne envie de lire.
                </small>

            </div>


            <!-- Barre d'outils décorative -->
            <div class="chapter-writing-toolbar">

                <span>✍️ Ton espace d'écriture</span>

                <span id="word-count">
                    0 mot
                </span>

            </div>


            <!-- Contenu du chapitre -->
            <div class="chapter-form-group">

                <label for="chapter-content">
                    Le contenu de ton chapitre
                </label>

                <textarea
                    id="chapter-content"
                    name="content"
                    rows="20"
                    maxlength="100000"
                    placeholder="Écris ici ton chapitre...

Décris les lieux, les émotions, les dialogues et les événements.

Laisse ton imagination raconter l'histoire..."
                    required
                ><?= e($content) ?></textarea>

                <small>
                    Ton texte sera enregistré en brouillon.
                </small>

            </div>


            <!-- Aperçu du texte -->
            <section class="chapter-preview">

                <div class="chapter-preview-heading">
                    <h2>Aperçu du chapitre</h2>

                    <button
                        type="button"
                        id="toggle-preview"
                        class="chapter-preview-button"
                    >
                        Afficher l'aperçu
                    </button>
                </div>

                <div
                    id="preview-content"
                    class="chapter-preview-content"
                    hidden
                >
                    <h3 id="preview-title">
                        Ton titre apparaîtra ici
                    </h3>

                    <p id="preview-text">
                        Le contenu de ton chapitre apparaîtra ici.
                    </p>
                </div>

            </section>


            <!-- Boutons d'action -->
            <div class="chapter-form-actions">

                <button
                    type="submit"
                    class="chapter-primary-button"
                >
                    💾 Enregistrer le brouillon
                </button>

                <a
                    href="storie.php"
                    class="chapter-secondary-button"
                >
                    Retour au catalogue
                </a>

            </div>

        </form>

    </section>

</main>


<!-- Fonctions interactives de l'éditeur -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const titleInput = document.getElementById('chapter-title');
    const contentInput = document.getElementById('chapter-content');

    const wordCount = document.getElementById('word-count');

    const previewButton = document.getElementById('toggle-preview');
    const previewContent = document.getElementById('preview-content');

    const previewTitle = document.getElementById('preview-title');
    const previewText = document.getElementById('preview-text');


    // Compte les mots du texte.
    function updateWordCount() {

        const text = contentInput.value.trim();

        if (text === '') {
            wordCount.textContent = '0 mot';
            return;
        }

        const words = text.split(/\s+/).filter(Boolean);

        wordCount.textContent =
            words.length + (words.length > 1 ? ' mots' : ' mot');

    }


    // Met à jour l'aperçu en utilisant du texte brut.
    // textContent empêche le navigateur d'interpréter
    // le contenu saisi comme du HTML.
    function updatePreview() {

        previewTitle.textContent =
            titleInput.value.trim() || 'Ton titre apparaîtra ici';

        previewText.textContent =
            contentInput.value || 'Le contenu apparaîtra ici';

    }


    // Actualisation pendant la saisie.
    contentInput.addEventListener('input', updateWordCount);

    titleInput.addEventListener('input', updatePreview);

    contentInput.addEventListener('input', updatePreview);


    // Affiche ou masque l'aperçu.
    previewButton.addEventListener('click', function () {

        const isHidden = previewContent.hidden;

        previewContent.hidden = !isHidden;

        previewButton.textContent = isHidden
            ? 'Masquer l’aperçu'
            : 'Afficher l’aperçu';

        updatePreview();

    });


    // Initialisation au chargement.
    updateWordCount();
    updatePreview();

});
</script>

<?php

// Pied de page commun de Sonora.
page_footer();

?>
