<?php
require_once 'functions.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $type = ($_POST['account_type'] ?? 'fan') === 'artist' ? 'artist' : 'fan';
    if (mb_strlen($username) < 3 || mb_strlen($username) > 40) $error = 'Le pseudo doit contenir entre 3 et 40 caractères.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Adresse e-mail invalide.';
    elseif (strlen($password) < 10) $error = 'Choisissez un mot de passe d’au moins 10 caractères.';
    else {
        try {
            $stmt = $pdo->prepare('INSERT INTO users(username,email,password_hash,account_type) VALUES(?,?,?,?)');
            $stmt->execute([$username,$email,password_hash($password, PASSWORD_DEFAULT),$type]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            header('Location: index.php'); exit;
        } catch (PDOException $e) {
            $error = 'Ce pseudo ou cet e-mail est déjà utilisé.';
        }
    }
}
page_header('Inscription');
?>
<div class="form-wrap">
<div class="eyebrow">REJOINS SONORA</div>
<h1>Crée ton compte</h1><p>Retrouve les fans et les artistes qui te font vibrer.</p>
<?php if ($error): ?>
    <div class="alert">
<?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-card"><label>Pseudo<input name="username" required minlength="3" maxlength="40" autocomplete="username"></label>
<label>Adresse e-mail<input type="email" name="email" required autocomplete="email"></label>
<label>Mot de passe<input type="password" name="password" required minlength="10" autocomplete="new-password"><small>10 caractères minimum.</small></label>
<label>Type de compte<select name="account_type"><option value="fan">Fan</option><option value="artist">Artiste (profil non vérifié)</option></select></label>
<button class="button" type="submit">Créer mon compte</button></form><p>Déjà inscrit ? <a href="connexion.php">Se connecter</a></p></div>
<?php page_footer(); ?>
