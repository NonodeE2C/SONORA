# Sonora · prototype PHP/MySQL

Sonora est une base de site communautaire musical. Elle tourne localement avec XAMPP, PHP et MySQL/MariaDB.

## Pages incluses
- Accueil, artistes, pages artistes, actualités
- Inscription, connexion, déconnexion, profil et paramètres
- Salons publics (chat), messages privés entre membres
- Concerts (données à ajouter), notifications
- Formulaire de signalement et page d’administration

## Installation Windows / XAMPP
1. Décompresse le dossier `sonora` dans `C:\xampp\htdocs\sonora`.
2. Lance Apache et MySQL dans le panneau XAMPP.
3. Ouvre `http://localhost/phpmyadmin`.
4. Importe `schema.sql` dans phpMyAdmin. Si tu avais déjà une ancienne base Sonora contenant des données, sauvegarde-la avant toute réinitialisation.
5. Vérifie `config.php` : les réglages par défaut sont `127.0.0.1`, base `sonora`, utilisateur `root`, mot de passe vide.
6. Va sur `http://localhost/sonora/`.

## Créer le premier administrateur
1. Inscris-toi normalement sur le site.
2. Dans phpMyAdmin, ouvre la base `sonora`, puis l'onglet SQL.
3. Exécute `UPDATE users SET is_admin = 1 WHERE email = 'TON-EMAIL';` en remplaçant par l'adresse utilisée lors de l'inscription.
4. Connecte-toi à nouveau et ouvre `admin.php`.

## Important
C'est un prototype d'apprentissage, pas encore un site prêt à accueillir le public. Avant publication, il faut notamment ajouter une protection CSRF à tous les formulaires, une limitation anti-spam, une procédure de récupération de compte, la validation des URLs, la politique de confidentialité, la modération, des tests de sécurité et un hébergement HTTPS. Les actualités et concerts ne sont pas importés automatiquement : seules les données ajoutées en base apparaissent. Le chat se recharge à l'actualisation, il n'est pas encore en temps réel.
