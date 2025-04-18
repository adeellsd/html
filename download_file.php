<?php
// Démarrage de la session
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Validation sécurisée du nom de fichier
if (isset($_GET['file'])) {
    $requestedFile = basename($_GET['file']);
    
    // Construction du chemin sécurisé
    $userDir = './files/' . $_SESSION['username'] . '/';
    $filePath = realpath($userDir . $requestedFile);
    
    // Vérification que le fichier existe et qu'il est dans le répertoire de l'utilisateur
    if ($filePath && file_exists($filePath) && is_file($filePath) && 
        strpos($filePath, realpath($userDir)) === 0) {
        
        // Configuration des en-têtes pour le téléchargement
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $requestedFile . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: public');
        
        // Envoyer le fichier et terminer le script
        readfile($filePath);
        exit;
    }
}

// En cas d'erreur ou si le fichier n'existe pas
header('Location: index.php?error=invalid_file');
exit();
?>
