<?php
session_start();

require 'bd.php';
require 'csrf.php';
require_once 'filter_config.php';

header('Content-Type: application/json');

requirePostMethod();
requireCsrfToken();

// verifica  connexion
if (!isset($_SESSION['client'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour envoyer un message'
    ]);
    exit;
}

$id_client = $_SESSION['client']['id_client'];
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// validation  message
if (empty($message)) {
    echo json_encode([
        'success' => false,
        'message' => 'Le message ne peut pas être vide'
    ]);
    exit;
}

if (strlen($message) > 256) {
    echo json_encode([
        'success' => false,
        'message' => 'Le message ne peut pas dépasser 256 caractères'
    ]);
    exit;
}

$filter_result = filterMessage($message);

if ($filter_result['is_offensive']) {
    error_log("Message bloqué (méthode: {$filter_result['method']}) de l'utilisateur $id_client");
    echo json_encode([
        'success' => false,
        'message' => 'Votre message contient du contenu inapproprié et n\'a pas été envoyé.',
        'offensive' => true,
        'score' => $filter_result['score']
    ]);
    exit;
}

// Message valide - insertion BD
$message_clean = strip_tags($message);
$bdd = getBD();

// Suppression messages > 10 min
$stmt_clean = $bdd->prepare("DELETE FROM messages WHERE date_envoi < DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
$stmt_clean->execute();

// Insertion
$stmt = $bdd->prepare("INSERT INTO messages (id_client, contenu, date_envoi) VALUES (:id_client, :contenu, NOW())");
$stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
$stmt->bindParam(':contenu', $message_clean, PDO::PARAM_STR);

if ($stmt->execute()) {
    error_log("Message envoyé par l'utilisateur $id_client: $message_clean");
    
    echo json_encode([
        'success' => true,
        'message' => 'Message envoyé avec succès'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'envoi du message'
    ]);
}

?>