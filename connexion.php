<?php
require_once 'functions.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id,password_hash FROM users WHERE email=?');
    $stmt->execute([$email]); $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true); $_SESSION['user_id'] = (int)$user['id'];
        header('Location: index.php'); exit;
    }
    $error = 'E-mail ou mot de passe incorrect.';
}
page_header('Connexion');
?>
<div class="form-wrap"><div class="eyebrow">HEUREUX DE TE REVOIR</div><h1>Connexion</h1>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-card"><label>Adresse e-mail<input type="email" name="email" required autocomplete="email"></label>
<label>Mot de passe<input type="password" name="password" required autocomplete="current-password"></label><button class="button" type="submit">Se connecter</button></form>
<p>Pas encore de compte ? <a href="inscription.php">Créer un compte</a></p></div>
<?php page_footer(); ?>
