<?php
require("forced.php");
// forced.php inclut déjà user_functions.php et lib_login.php
require_once("message_functions.php");

// Initialisation des variables de réponse
$response = array(
    'success' => false,
    'message' => 'Une erreur est survenue.'
);

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Méthode non autorisée.';
    echo json_encode($response);
    exit;
}

// Vérification de l'authentification - CORRECTION ICI
if (!isset($__connected) || !$__connected || empty($__connected["username"])) {
    error_log("Utilisateur non connecté ou username vide: " . print_r($__connected, true));
    $response['message'] = 'Vous devez être connecté pour envoyer un message.';
    echo json_encode($response);
    exit;
}

// Vérification des données requises
if (!isset($_POST['receiver_username']) || !isset($_POST['content']) || !isset($_POST['csrf_token'])) {
    $response['message'] = 'Données manquantes.';
    echo json_encode($response);
    exit;
}

// Validation du jeton CSRF
if (!verify_csrf_token($_POST['csrf_token'])) {
    $response['message'] = 'Jeton de sécurité invalide.';
    echo json_encode($response);
    exit;
}

// Récupération et nettoyage des données
$sender_username = $__connected["username"];
error_log("Nom d'utilisateur expéditeur: " . $sender_username); // Ajout du log pour vérifier

$receiver_username = trim($_POST['receiver_username']);
$content = trim($_POST['content']);

// Validation des données
if (empty($sender_username)) {
    error_log("Erreur: nom d'expéditeur vide après récupération");
    $response['message'] = 'Erreur d\'identification. Veuillez vous reconnecter.';
    echo json_encode($response);
    exit;
}

if (empty($receiver_username) || empty($content)) {
    $response['message'] = 'Le destinataire et le contenu du message ne peuvent pas être vides.';
    echo json_encode($response);
    exit;
}

// Vérification de l'existence du destinataire
if (!search_user($receiver_username)) {
    $response['message'] = 'Destinataire introuvable.';
    echo json_encode($response);
    exit;
}

// Limitation de la longueur du message
if (strlen($content) > 1000) {
    $response['message'] = 'Le message est trop long (1000 caractères maximum).';
    echo json_encode($response);
    exit;
}

// Envoi du message
if (send_message($sender_username, $receiver_username, $content)) {
    $response['success'] = true;
    $response['message'] = 'Message envoyé avec succès.';
} else {
    $response['message'] = 'Erreur lors de l\'envoi du message.';
}

// Retour de la réponse en JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>