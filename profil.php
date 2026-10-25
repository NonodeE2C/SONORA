<?php
require_once 'functions.php';
require_login();
$user=current_user($pdo);
$stmt=$pdo->prepare('SELECT a.* FROM follows f JOIN artists a ON a.id=f.artist_id WHERE f.user_id=? ORDER BY a.name'); $stmt->execute([$user['id']]); $artists=$stmt->fetchAll();
page_header('Mon profil');
?><div class="eyebrow">MON ESPACE</div><h1>Bienvenue, <?= e($user['username']) ?> !</h1><div class="profile-card"><p><strong>E-mail :</strong> <?= e($user['email']) ?></p><p><strong>Type de compte :</strong> <?= $user['account_type']==='artist' ? 'Artiste (non vérifié)' : 'Fan' ?></p></div>
<section class="section-head"><div><div class="eyebrow">TA SÉLECTION</div><h2>Artistes suivis</h2></div></section>
<div class="artist-grid"><?php foreach ($artists as $a): ?><a class="artist-card" style="--accent:<?= e($a['accent_color']) ?>" href="artiste.php?id=<?= (int)$a['id'] ?>"><div class="artist-art"><span>♫</span></div><div class="artist-card-body"><h3><?= e($a['name']) ?></h3><p><?= e($a['genre']) ?></p></div></a><?php endforeach; ?></div>
<?php if (!$artists): ?><p>Tu ne suis encore aucun artiste. <a href="artistes.php">Découvre le catalogue</a>.</p><?php endif; ?>
<p class="actions"><a class="button secondary" href="parametres.php">Modifier mon profil</a><a class="button secondary" href="signaler.php">Signaler un contenu</a><?php if (!empty($user['is_admin'])): ?><a class="button secondary" href="admin.php">Administration</a><?php endif; ?></p><?php page_footer(); ?>
