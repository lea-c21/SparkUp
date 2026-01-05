<?php
session_start();
require_once '../bd.php';
require_once '../csrf.php';
require_once '../filter_config.php';

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
$id_parent = isset($_POST['id_parent']) ? intval($_POST['id_parent']) : null;
$contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';

// validations
if ($id_question <= 0) {
    echo json_encode(['success' => false, 'message' => 'Question invalide']);
    exit;
}

if (empty($contenu)) {
    echo json_encode(['success' => false, 'message' => 'La réponse ne peut pas être vide']);
    exit;
}

// filtrage
$filter_result = filterMessage($contenu);

if ($filter_result['is_offensive']) {
    error_log("Réponse bloquée (méthode: {$filter_result['method']}) de l'utilisateur $id_client");
    echo json_encode([
        'success' => false,
        'message' => 'Votre réponse contient du contenu inapproprié.'
    ]);
    exit;
}


$bdd = getBD();

// verifica que la question existe
$stmt = $bdd->prepare("SELECT COUNT(*) FROM questions WHERE id_question = ?");
$stmt->execute([$id_question]);
if ($stmt->fetchColumn() == 0) {
    echo json_encode(['success' => false, 'message' => 'Question non trouvée']);
    exit;
}

// parent existe?
if ($id_parent !== null && $id_parent > 0) {
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM reponses WHERE id_reponse = ?");
    $stmt->execute([$id_parent]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Réponse parente non trouvée']);
        exit;
    }
} else {
    $id_parent = null;
}

// nettoyage XSS
$contenu_clean = strip_tags($contenu);

// insertion reply
$stmt = $bdd->prepare("
    INSERT INTO reponses (id_question, id_client, id_parent, contenu, date_creation) 
    VALUES (?, ?, ?, ?, NOW())
");

if ($stmt->execute([$id_question, $id_client, $id_parent, $contenu_clean])) {
    echo json_encode([
        'success' => true,
        'message' => 'Votre réponse a étépubliée avec succés!'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la publication'
    ]);
}
    
?>