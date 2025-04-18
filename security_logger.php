<?php
/**
 * Système de journalisation de sécurité
 * Enregistre les événements de sécurité importants pour analyse ultérieure
 */

// Constantes de niveau de log
define('LOG_LEVEL_INFO', 'INFO');
define('LOG_LEVEL_WARNING', 'WARNING');
define('LOG_LEVEL_ERROR', 'ERROR');
define('LOG_LEVEL_CRITICAL', 'CRITICAL');

/**
 * Journalise un événement de sécurité
 * 
 * @param string $level Niveau de l'événement (INFO, WARNING, ERROR, CRITICAL)
 * @param string $message Description de l'événement
 * @param array $context Données contextuelles supplémentaires
 * @return bool Succès ou échec de l'enregistrement
 */
function security_log($level, $message, $context = []) {
    // Créer le répertoire de logs s'il n'existe pas
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0750, true);
    }
    
    // Protéger le répertoire de logs avec un .htaccess
    $htaccessFile = $logDir . '/.htaccess';
    if (!file_exists($htaccessFile)) {
        file_put_contents($htaccessFile, "Deny from all\n");
    }
    
    // Construire l'entrée de log
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user = isset($_SESSION['username']) ? $_SESSION['username'] : 'anonymous';
    
    // Formater le contexte
    $contextStr = '';
    if (!empty($context)) {
        // Filtrer les données sensibles
        if (isset($context['password'])) $context['password'] = '***REDACTED***';
        if (isset($context['token'])) $context['token'] = '***REDACTED***';
        
        $contextStr = json_encode($context);
    }
    
    // Construire la ligne de log
    $logEntry = sprintf(
        "[%s] [%s] [IP: %s] [User: %s] %s %s\n",
        $timestamp,
        $level,
        $ip,
        $user,
        $message,
        $contextStr
    );
    
    // Écrire dans le fichier de log quotidien
    $logFile = $logDir . '/security_' . date('Y-m-d') . '.log';
    return file_put_contents($logFile, $logEntry, FILE_APPEND) !== false;
}

/**
 * Journalise une tentative de connexion
 * 
 * @param string $username Nom d'utilisateur utilisé
 * @param bool $success Succès ou échec de la tentative
 * @param string $reason Raison de l'échec (optionnel)
 */
function log_login_attempt($username, $success, $reason = '') {
    $level = $success ? LOG_LEVEL_INFO : LOG_LEVEL_WARNING;
    $message = $success 
        ? "Connexion réussie" 
        : "Échec de connexion: $reason";
    
    security_log($level, $message, [
        'username' => $username, 
        'successful' => $success
    ]);
}

/**
 * Journalise des actions sensibles (suppression, modification de privilèges, etc.)
 * 
 * @param string $action Description de l'action
 * @param array $details Détails supplémentaires sur l'action
 */
function log_sensitive_action($action, $details = []) {
    security_log(LOG_LEVEL_INFO, "Action sensible: $action", $details);
}

/**
 * Journalise une tentative d'accès non autorisé
 * 
 * @param string $resource Ressource concernée
 * @param string $details Détails supplémentaires
 */
function log_unauthorized_access($resource, $details = '') {
    security_log(LOG_LEVEL_WARNING, "Accès non autorisé à: $resource", [
        'details' => $details
    ]);
}

/**
 * Journalise une erreur de sécurité critique
 * 
 * @param string $description Description de l'erreur
 * @param array $context Contexte de l'erreur
 */
function log_security_critical($description, $context = []) {
    security_log(LOG_LEVEL_CRITICAL, $description, $context);
}
?>