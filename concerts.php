<?php
require_once 'functions.php';
$rows=$pdo->query('SELECT c.*,a.name AS artist_name FROM concerts c JOIN artists a ON a.id=c.artist_id WHERE c.concert_date >= NOW() ORDER BY c.concert_date ASC')->fetchAll();
page_header('Concerts'); ?>
<section class="page-intro"><div class="eyebrow">SUR SCÈNE</div><h1>Les prochains concerts</h1><p>Retrouve les dates ajoutées à Sonora. Les billets doivent toujours être achetés sur un site officiel.</p></section>
<div class="news-list"><?php foreach($rows as $c): ?><article class="news-card"><div class="news-meta"><?= e(date('d/m/Y à H:i',strtotime($c['concert_date']))) ?> · <?= e($c['city']) ?></div><h3><?= e($c['artist_name']) ?> · <?= e($c['venue']) ?></h3><?php if($c['ticket_url']): ?><a href="<?= e($c['ticket_url']) ?>" target="_blank" rel="noopener noreferrer">Billetterie / informations ↗</a><?php endif; ?></article><?php endforeach; ?><?php if(!$rows): ?><article class="news-card"><h3>La scène est encore vide 🎤</h3><p>Aucun concert n'a été ajouté pour le moment. Les dates pourront être renseignées depuis l'administration.</p></article><?php endif; ?></div>
<?php page_footer(); ?>