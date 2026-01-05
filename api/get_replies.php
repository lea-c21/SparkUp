<?php
session_start();
require_once '../bd.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$id_question = isset($_GET['id_question']) ? intval($_GET['id_question']) : 0;

if ($id_question <= 0) {
    echo json_encode(['success' => false, 'message' => 'Question invalide']);
    exit;
}


$bdd = getBD();

// recuperation replies
$stmt = $bdd->prepare("
    SELECT 
        r.*,
        cl.prenom,
        cl.nom
    FROM reponses r
    INNER JOIN client cl ON r.id_client = cl.id_client
    WHERE r.id_question = ?
    ORDER BY r.date_creation ASC
");

$stmt->execute([$id_question]);
$reponses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ajout info reply
$current_user_id = isset($_SESSION['client']) ? $_SESSION['client']['id_client'] : 0;

foreach ($reponses as &$reponse) {
    $reponse['is_owner'] = ($reponse['id_client'] == $current_user_id);
    $reponse['can_reply'] = isset($_SESSION['client']);
}

echo json_encode([
    'success' => true,
    'replies' => $reponses
]);
    
?>