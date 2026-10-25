<?php
declare(strict_types=1);
session_start();

$host = '127.0.0.1';
$dbname = 'sonora';
$dbuser = 'root';
$dbpass = ''; // Réglage habituel XAMPP local. À changer selon votre installation.

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Connexion à la base impossible. Vérifiez config.php et importez schema.sql.');
}
