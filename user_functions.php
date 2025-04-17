<?php
/**
 * Fonctions relatives à la gestion des utilisateurs
 */

/**
 * Recherche un utilisateur dans la base de données
 * @param string $username - Nom d'utilisateur à rechercher
 * @return array|false - Tableau contenant les informations de l'utilisateur ou false si non trouvé
 */
function search_user($username) {
    if (empty($username)) {
        return false;
    }
    
    try {
        // Récupération des infos de la base de données
        $dbi = get_db_infos();
        $conn = new mysqli($dbi["srv"], $dbi["usr"], $dbi["pwd"], $dbi["db"]);
        
        if ($conn->connect_error) {
            error_log("Erreur de connexion à la base de données: " . $conn->connect_error);
            return false;
        }
        
        // Utilisation de requêtes préparées pour éviter les injections SQL
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        if (!$stmt) {
            error_log("Erreur de préparation de la requête: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            $conn->close();
            return false;
        }
        
        $user_data = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        return $user_data;
    } catch (Exception $e) {
        error_log("Exception lors de la recherche d'utilisateur: " . $e->getMessage());
        return false;
    }
}

/**
 * Vérifie si un utilisateur existe dans la base de données
 * @param string $username - Nom d'utilisateur à vérifier
 * @return bool - True si l'utilisateur existe, false sinon
 */
function user_exists($username) {
    return search_user($username) !== false;
}

/**
 * Récupère la liste de tous les utilisateurs
 * @return array - Tableau contenant tous les utilisateurs
 */
function get_all_users() {
    try {
        // Récupération des infos de la base de données
        $dbi = get_db_infos();
        $conn = new mysqli($dbi["srv"], $dbi["usr"], $dbi["pwd"], $dbi["db"]);
        
        if ($conn->connect_error) {
            error_log("Erreur de connexion à la base de données: " . $conn->connect_error);
            return [];
        }
        
        $stmt = $conn->prepare("SELECT username, admin FROM users ORDER BY username");
        if (!$stmt) {
            error_log("Erreur de préparation de la requête: " . $conn->error);
            return [];
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return $users;
    } catch (Exception $e) {
        error_log("Exception lors de la récupération des utilisateurs: " . $e->getMessage());
        return [];
    }
}
?>