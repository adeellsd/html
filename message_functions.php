<?php
/**
 * Fonctions de gestion des messages
 * Contient les fonctions pour envoyer, récupérer et gérer les messages
 */

/**
 * Envoie un message d'un utilisateur à un autre
 * @param string $sender_username - Nom d'utilisateur de l'expéditeur
 * @param string $receiver_username - Nom d'utilisateur du destinataire
 * @param string $content - Contenu du message
 * @return bool - True si le message a été envoyé, False sinon
 */
function send_message($sender_username, $receiver_username, $content) {
    // Enregistrement pour le débogage
    error_log("Tentative d'envoi de message de $sender_username à $receiver_username");
    
    // Vérification des paramètres
    if (empty($sender_username) || empty($receiver_username) || empty($content)) {
        error_log("Paramètres manquants pour l'envoi du message");
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
        
        // Vérification si la table existe
        $result = $conn->query("SHOW TABLES LIKE 'messages'");
        if ($result->num_rows == 0) {
            // La table n'existe pas, on la crée
            error_log("La table messages n'existe pas, création...");
            $sql = "CREATE TABLE `messages` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `sender_username` varchar(255) NOT NULL,
                `receiver_username` varchar(255) NOT NULL,
                `content` text NOT NULL,
                `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `read_status` tinyint(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `sender_username` (`sender_username`),
                KEY `receiver_username` (`receiver_username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            if (!$conn->query($sql)) {
                error_log("Erreur lors de la création de la table: " . $conn->error);
                return false;
            }
        }
        
        // Utilisation de requêtes préparées pour éviter les injections SQL
        $stmt = $conn->prepare("INSERT INTO `messages` (`sender_username`, `receiver_username`, `content`) VALUES (?, ?, ?)");
        if (!$stmt) {
            error_log("Erreur de préparation de la requête: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("sss", $sender_username, $receiver_username, $content);
        
        $result = $stmt->execute();
        if (!$result) {
            error_log("Erreur d'exécution de la requête: " . $stmt->error);
        } else {
            error_log("Message envoyé avec succès");
        }
        
        $stmt->close();
        $conn->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Exception lors de l'envoi du message: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère les messages reçus par un utilisateur
 * @param string $username - Nom d'utilisateur du destinataire
 * @return array - Tableau contenant les messages ou tableau vide si aucun message
 */
function get_received_messages($username) {
    // Vérification des paramètres
    if (empty($username)) {
        return [];
    }
    
    try {
        // Récupération des infos de la base de données
        $dbi = get_db_infos();
        $conn = new mysqli($dbi["srv"], $dbi["usr"], $dbi["pwd"], $dbi["db"]);
        
        if ($conn->connect_error) {
            error_log("Erreur de connexion à la base de données: " . $conn->connect_error);
            return [];
        }
        
        // Vérification si la table existe
        $result = $conn->query("SHOW TABLES LIKE 'messages'");
        if ($result->num_rows == 0) {
            // La table n'existe pas encore
            return [];
        }
        
        // Utilisation de requêtes préparées pour éviter les injections SQL
        $stmt = $conn->prepare("SELECT * FROM `messages` WHERE `receiver_username` = ? ORDER BY `timestamp` DESC");
        if (!$stmt) {
            error_log("Erreur de préparation de la requête: " . $conn->error);
            return [];
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return $messages;
    } catch (Exception $e) {
        error_log("Exception lors de la récupération des messages: " . $e->getMessage());
        return [];
    }
}

/**
 * Marque un message comme lu
 * @param int $message_id - Identifiant du message
 * @param string $username - Nom d'utilisateur du destinataire (pour vérifier l'autorisation)
 * @return bool - True si le message a été marqué comme lu, False sinon
 */
function mark_message_as_read($message_id, $username) {
    // Vérification des paramètres
    if (empty($message_id) || empty($username)) {
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
        
        // Vérifier que l'utilisateur est bien le destinataire du message
        $stmt = $conn->prepare("SELECT * FROM `messages` WHERE `id` = ? AND `receiver_username` = ?");
        if (!$stmt) {
            error_log("Erreur de préparation de la requête: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("is", $message_id, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows !== 1) {
            $stmt->close();
            $conn->close();
            return false;
        }
        
        // Marquer le message comme lu
        $stmt = $conn->prepare("UPDATE `messages` SET `read_status` = 1 WHERE `id` = ?");
        if (!$stmt) {
            error_log("Erreur de préparation de la requête de mise à jour: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("i", $message_id);
        $result = $stmt->execute();
        
        $stmt->close();
        $conn->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Exception lors du marquage du message comme lu: " . $e->getMessage());
        return false;
    }
}

/**
 * Génère un jeton CSRF
 * @return string - Jeton CSRF
 */
function generate_csrf_token() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie si le jeton CSRF est valide
 * @param string $token - Jeton CSRF à vérifier
 * @return bool - True si le jeton est valide, False sinon
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}
?>