<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function logged_in(): bool {
    return isset($_SESSION['user_id']);
}
function require_login(): void {
    if (!logged_in()) {
        header('Location: connexion.php');
        exit;
    }
}
function current_user(PDO $pdo): ?array {
    if (!logged_in()) return null;
    $stmt = $pdo->prepare('SELECT id, username, email, account_type, bio, is_admin FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}
function page_header(string $title): void {
    global $pdo;
    $user = current_user($pdo);
    ?><!doctype html>
    <html lang="fr">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?= e($title) ?> · Sonora</title>
            <link rel="stylesheet" href="style.css">
        </head>
        <body>
            <header class="topbar">
                <a class="brand" href="index.php">
                    <span class="brand-icon">♫</span> SONORA
                </a>
                <nav>
                    <a href="actualites.php">Actualités</a>
                    <a href="artistes.php">Artistes</a>
                    <a href="concerts.php">Concerts</a>
                    <a href="communaute.php">Communauté</a>
                    <a href="storie.php">Histoires</a>
                    <?php if ($user): ?>
                        <a href="profil.php"><?= e($user['username']) ?></a>
                        <a href="messages.php">Messages</a>
                        <a href="notifications.php">Notifications</a>
                        <a href="deconnexion.php">Déconnexion</a>
                    <?php else: ?>
                        <a href="connexion.php">Connexion</a>
                        <a class="nav-cta" href="inscription.php">Créer un compte</a>
                    <?php endif; ?>
    </nav></header><main class="container"><?php
}
function page_footer(): void {
    ?></main><footer class="footer">SONORA · La musique nous rassemble. <span>Prototype en développement</span></footer></body></html><?php
}
