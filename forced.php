<?php
session_start();

// Importation des fonctions utilisateur et d'authentification
require_once("user_functions.php");
require_once("lib_login.php");

// Inclure les fichiers de sécurité
require_once('security_headers.php');
require_once('security_logger.php');

$__connected = array(
    "username" => $_SESSION["username"] ?? null,
    "ADMIN" => $_SESSION["admin"] ?? 0
);

if (! $__connected["username"]) {
    if (! isset($LOGIN_PAGE)) {
        header("Location: /login.php");
        die();
    }
}

?>