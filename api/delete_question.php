<?php
session_start();
require_once '../bd.php';
require_once '../csrf.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF invalide']);
    exit;
}

if (!isset($_SESSION['client'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté']);
    exit;
}

$id_client = $_SESSION['client']['id_client'];
$id_question = isset($_POST['id_question']) ? intval($_POST['id_question']) : 0;

if ($id_question <= 0) {
    echo json_encode(['success' => false, 'message' => 'Question invalide']);
    exit;
}


$bdd = getBD();

// question de l'utilisateur?
$stmt = $bdd->prepare("SELECT id_client FROM questions WHERE id_question = ?");
$stmt->execute([$id_question]);
$question = $stmt->fetch();

if (!$question) {
    echo json_encode(['success' => false, 'message' => 'Question non trouvée']);
    exit;
}

if ($question['id_client'] != $id_client) {
    echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer cette question']);
    exit;
}

// suppression reponses
$stmt = $bdd->prepare("DELETE FROM reponses WHERE id_question = ?");
$stmt->execute([$id_question]);

// suppression question
$stmt = $bdd->prepare("DELETE FROM questions WHERE id_question = ?");

if ($stmt->execute([$id_question])) {
    echo json_encode([
        'success' => true,
        'message' => 'Question supprimée avec succès'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression'
    ]);
}
    

?>