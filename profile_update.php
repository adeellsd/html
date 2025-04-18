<?php
// Démarrage de la session
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Génération d'un token CSRF s'il n'existe pas déjà
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Inclusion des fichiers nécessaires
require_once('lib_login.php');

// Initialisation des variables
$success = false;
$errors = [];
$currentUser = $_SESSION['username'];

// Traitement du formulaire de mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Erreur de validation du formulaire.';
    } else {
        // Validation et nettoyage des entrées
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Mise à jour du profil
        if (!empty($description)) {
            // Mettre à jour la description (code à implémenter)
            // update_user_description($currentUser, $description);
            $success = true;
        }
        
        // Mise à jour du mot de passe
        if (!empty($oldPassword) && !empty($newPassword)) {
            // Vérification de l'ancien mot de passe
            if (verify_user_password($currentUser, $oldPassword)) {
                // Validation du nouveau mot de passe
                if (strlen($newPassword) < 8) {
                    $errors[] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
                } elseif ($newPassword !== $confirmPassword) {
                    $errors[] = 'Les mots de passe ne correspondent pas.';
                } else {
                    // Hachage du nouveau mot de passe
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                    
                    // Mise à jour du mot de passe
                    // update_user_password($currentUser, $hashedPassword);
                    $success = true;
                    
                    // Régénération de l'ID de session après changement de mot de passe
                    session_regenerate_id(true);
                }
            } else {
                $errors[] = 'L\'ancien mot de passe est incorrect.';
            }
        }
    }
}

// Récupération des informations du profil actuel pour l'affichage
// $userProfile = get_user_profile($currentUser);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Mon Profil</h1>
        
        <?php if ($success): ?>
            <div class="success-message">
                <p>Votre profil a été mis à jour avec succès.</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            
            <fieldset>
                <legend>Informations personnelles</legend>
                
                <div class="form-group">
                    <label for="username">Nom d'utilisateur:</label>
                    <input type="text" id="username" value="<?= htmlspecialchars($currentUser) ?>" disabled>
                    <small>Le nom d'utilisateur ne peut pas être modifié.</small>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3"><?= isset($userProfile['description']) ? htmlspecialchars($userProfile['description']) : '' ?></textarea>
                </div>
            </fieldset>
            
            <fieldset>
                <legend>Changer de mot de passe</legend>
                
                <div class="form-group">
                    <label for="old_password">Ancien mot de passe:</label>
                    <input type="password" id="old_password" name="old_password">
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe:</label>
                    <input type="password" id="new_password" name="new_password">
                    <small>Minimum 8 caractères</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le nouveau mot de passe:</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
            </fieldset>
            
            <div class="form-group">
                <button type="submit">Mettre à jour</button>
            </div>
        </form>
        
        <p><a href="index.php">Retour à l'accueil</a></p>
    </div>
</body>
</html>