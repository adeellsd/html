<?php
// Démarrage de la session
session_start();

// Importation des fichiers nécessaires
require_once("lib_login.php");

// Vérification de l'authentification et des droits d'administrateur
if (!isset($_SESSION['username']) || $_SESSION['admin'] != 1) {
    header('Location: login.php');
    exit();
}

// Génération d'un token CSRF s'il n'existe pas déjà
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement de la demande de suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Location: admin.php?error=csrf');
        exit();
    }
    
    // Validation et nettoyage du nom d'utilisateur
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // Vérification que l'utilisateur n'essaie pas de se supprimer lui-même
    if ($username === $_SESSION['username']) {
        header('Location: admin.php?error=self_delete');
        exit();
    }
    
    // Suppression de l'utilisateur
    if (delete_user($username)) {
        // Suppression réussie
        header('Location: admin.php?success=user_deleted');
    } else {
        // Erreur lors de la suppression
        header('Location: admin.php?error=delete_failed');
    }
    exit();
}

// Redirection si aucune action n'est spécifiée
header('Location: admin.php');
exit();
?>