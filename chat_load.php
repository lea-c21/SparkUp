<?php
session_start();

require 'bd.php';

header('Content-Type: application/json');

if (!isset($_SESSION['client'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté'
    ]);
    exit;
}


$bdd = getBD();

// suppression mess + 10 minutes
$stmt_clean = $bdd->prepare("DELETE FROM messages WHERE date_envoi < DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
$stmt_clean->execute();

// recup mess recents
$stmt = $bdd->prepare("
    SELECT 
        m.id_message,
        m.id_client,
        m.contenu,
        m.date_envoi,
        c.prenom,
        c.nom,
        TIMESTAMPDIFF(SECOND, m.date_envoi, NOW()) as secondes_ecoule
    FROM messages m
    INNER JOIN client c ON m.id_client = c.id_client
    WHERE m.date_envoi >= DATE_SUB(NOW(), INTERVAL 10 MINUTE)
    ORDER BY m.date_envoi ASC
");

$stmt->execute();
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// formater les messages avec le temps écoulé
foreach ($messages as &$msg) {
    $secondes = intval($msg['secondes_ecoule']);
    
    if ($secondes > 60) {
        $minutes = floor($secondes / 60);
        $msg['temps_ecoule'] = "Il y a $minutes min";
    }else{
        $msg['temps_ecoule'] = "Il y a $secondes sec";
    }
    
    // nettoyer les données sensibles
    unset($msg['secondes_ecoule']);
    unset($msg['nom']); // On garde seulement le prénom
}

echo json_encode([
    'success' => true,
    'messages' => $messages,
    'count' => count($messages)
]);


?>