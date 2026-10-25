<?php
require_once 'functions.php';
$rooms=$pdo->query('SELECT r.*, a.name AS artist_name, (SELECT COUNT(*) FROM chat_messages m WHERE m.room_id=r.id) AS message_count FROM chat_rooms r LEFT JOIN artists a ON a.id=r.artist_id ORDER BY a.name, r.name')->fetchAll();
page_header('Communauté');
?><div class="eyebrow">ON PARLE MUSIQUE</div><h1>La communauté</h1><p>Choisis un salon et échange avec les autres fans.</p>
<div class="room-grid"><?php foreach ($rooms as $r): ?><a class="room-card" href="chat.php?room=<?= (int)$r['id'] ?>"><span class="room-icon"><span>♫</span></span><div><h3><?= e($r['name']) ?></h3><p><?= e($r['description']) ?></p><small><?= (int)$r['message_count'] ?> message(s)</small></div><span>→</span></a><?php endforeach; ?></div>
<?php page_footer(); ?>
