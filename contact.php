<?php
// Démarrage de la session
session_start();

// Génération d'un token CSRF s'il n'existe pas déjà
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialisation des variables
$errors = [];
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Erreur de validation du formulaire.';
    } else {
        // Validation et nettoyage des entrées
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        // Validation du nom
        if (!$name || strlen($name) < 2 || strlen($name) > 50) {
            $errors[] = 'Le nom doit contenir entre 2 et 50 caractères.';
        }
        
        // Validation de l'email
        if (!$email) {
            $errors[] = 'Veuillez fournir une adresse email valide.';
        }
        
        // Validation du sujet
        if (!$subject || strlen($subject) < 5 || strlen($subject) > 100) {
            $errors[] = 'Le sujet doit contenir entre 5 et 100 caractères.';
        }
        
        // Validation du message
        if (!$message || strlen($message) < 10 || strlen($message) > 2000) {
            $errors[] = 'Le message doit contenir entre 10 et 2000 caractères.';
        }
        
        // Si aucune erreur, traiter le message
        if (empty($errors)) {
            // Ici, on pourrait envoyer un email ou enregistrer le message dans une base de données
            // Fonction d'envoi d'email sécurisée
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Contactez-nous</h1>
        
        <?php if ($success): ?>
            <div class="success-message">
                <p>Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.</p>
            </div>
        <?php else: ?>
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
                
                <div class="form-group">
                    <label for="name">Nom:</label>
                    <input type="text" id="name" name="name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Sujet:</label>
                    <input type="text" id="subject" name="subject" value="<?= isset($subject) ? htmlspecialchars($subject) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" rows="5" required><?= isset($message) ? htmlspecialchars($message) : '' ?></textarea>
                </div>
                
                <div class="form-group">
                    <button type="submit">Envoyer</button>
                </div>
            </form>
        <?php endif; ?>
        
        <p><a href="index.php">Retour à l'accueil</a></p>
    </div>
</body>
</html>