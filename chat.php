<?php
require_once 'functions.php';
require_login();
$roomId = filter_input(INPUT_GET, 'room', FILTER_VALIDATE_INT);
$stmt=$pdo->prepare('SELECT r.*, a.name AS artist_name FROM chat_rooms r LEFT JOIN artists a ON a.id=r.artist_id WHERE r.id=?');
$stmt->execute([$roomId ?: 0]); $room=$stmt->fetch();
if (!$room) { page_header('Salon introuvable'); echo '<h1>Salon introuvable</h1><a href="communaute.php">Retour à la communauté</a>'; page_footer(); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $message=trim($_POST['message'] ?? '');
    if ($message==='' || mb_strlen($message)>1000) $error='Le message doit contenir entre 1 et 1000 caractères.';
    else {
        $stmt=$pdo->prepare('INSERT INTO chat_messages(room_id,user_id,message) VALUES(?,?,?)');
        $stmt->execute([$room['id'],$_SESSION['user_id'],$message]);
        header('Location: chat.php?room='.(int)$room['id']); exit;
    }
}
$stmt=$pdo->prepare('SELECT m.message,m.created_at,u.username FROM chat_messages m JOIN users u ON u.id=m.user_id WHERE m.room_id=? ORDER BY m.created_at DESC LIMIT 100');
$stmt->execute([$room['id']]); $messages=array_reverse($stmt->fetchAll());
page_header($room['name']);
?><a href="communaute.php">← Tous les salons</a><h1><?= e($room['name']) ?></h1><p><?= e($room['description']) ?></p>
<div class="chat-box"><?php foreach ($messages as $m): ?><article class="chat-message"><div><strong><?= e($m['username']) ?></strong> <small><?= e(date('d/m H:i',strtotime($m['created_at']))) ?></small></div><p><?= nl2br(e($m['message'])) ?></p></article><?php endforeach; ?><?php if (!$messages): ?><p>Le salon est calme pour le moment. Lance la conversation !</p><?php endif; ?></div>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="message-form"><label for="message">Ton message</label><textarea id="message" name="message" maxlength="1000" required placeholder="Parle musique, reste respectueux…"></textarea><button class="button" type="submit">Envoyer</button></form>
<?php page_footer(); ?>
