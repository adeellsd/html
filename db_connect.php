<?php
/**
 * Établit une connexion à la base de données en utilisant des informations
 * stockées de manière sécurisée
 * 
 * @return mysqli - Objet de connexion à la base de données
 */
function db_connect() {
    // Définir les informations de connexion dans un fichier de configuration protégé
    $config = include(__DIR__ . '/config/db_config.php');
    
    // Établir la connexion
    $conn = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database']
    );
    
    // Vérifier la connexion
    if ($conn->connect_error) {
        error_log('Database connection failed: ' . $conn->connect_error);
        die('Erreur de connexion à la base de données');
    }
    
    // Définir le jeu de caractères
    $conn->set_charset('utf8mb4');
    
    return $conn;
}

/**
 * Ferme la connexion à la base de données
 * 
 * @param mysqli $conn - Objet de connexion à la base de données
 */
function db_close($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>