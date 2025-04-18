<?php
// Démarrage de la session
session_start();

// Vérification de l'authentification via forced.php
require_once("forced.php");

// Génération d'un token CSRF s'il n'existe pas déjà
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement de la demande de suppression (méthode POST pour plus de sécurité)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file'])) {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Échec de la vérification CSRF
        header('Location: index.php');
        exit();
    }
    
    // Validation du fichier à supprimer
    $fileName = basename($_POST['file']);
    
    // Construction du chemin
    $userDir = './files/' . $__connected["username"] . '/';
    $filePath = realpath($userDir . $fileName);
    $descFilePath = $filePath . '.alexdescfile';
    
    // Vérification que le fichier existe et qu'il est dans le répertoire de l'utilisateur
    if ($filePath && file_exists($filePath) && is_file($filePath) && 
        strpos($filePath, realpath($userDir)) === 0) {
        
        // Suppression du fichier
        if (unlink($filePath)) {
            // Suppression du fichier de description s'il existe
            if (file_exists($descFilePath)) {
                unlink($descFilePath);
            }
            
            // Journalisation (si security_logger.php est disponible)
            if (file_exists('security_logger.php')) {
                require_once('security_logger.php');
                log_sensitive_action("Suppression de fichier", ["file" => $fileName]);
            }
            
            header('Location: index.php');
            exit();
        }
    }
    
    // En cas d'échec
    header('Location: index.php');
    exit();
}

// Support pour le mode GET existant (moins sécurisé, à déprécier)
// Cette partie maintient la compatibilité avec l'ancienne méthode mais devrait être retirée à terme
if (isset($_GET['file'])) {
    // Validation du fichier à supprimer
    $fileName = basename($_GET['file']);
    
    // Construction du chemin
    $userDir = './files/' . $__connected["username"] . '/';
    $filePath = $userDir . $fileName;
    $descFilePath = $filePath . '.alexdescfile';
    
    // Vérification que le fichier existe
    if (file_exists($filePath) && is_file($filePath)) {
        // Suppression du fichier et de sa description
        if (unlink($filePath)) {
            // Suppression du fichier de description s'il existe
            if (file_exists($descFilePath)) {
                unlink($descFilePath);
            }
            
            header('Location: index.php');
            exit();
        }
    }
    
    // En cas d'échec
    header('Location: index.php');
    exit();
}

// Si aucune action n'est spécifiée, rediriger vers la page d'accueil
header('Location: index.php');
exit();
?>
