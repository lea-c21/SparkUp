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
$id_art = isset($_POST['id_art']) ? intval($_POST['id_art']) : 0;
$titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
$contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';

// validation question
if ($id_art <= 0) {
    echo json_encode(['success' => false, 'message' => 'Article invalide']);
    exit;
}

if (empty($titre)) {
    echo json_encode(['success' => false, 'message' => 'Le titre ne peut pas être vide']);
    exit;
}

if (empty($contenu)) {
    echo json_encode(['success' => false, 'message' => 'La question ne peut pas être vide']);
    exit;
}

// filtrage ai (titre et contenu)
$full_text = $titre . ' ' . $contenu;
$filter_result = filterMessage($full_text);

if ($filter_result['is_offensive']) {
    error_log("Question bloquée (méthode: {$filter_result['method']}) de l'utilisateur $id_client");
    echo json_encode([
        'success' => false,
        'message' => 'Votre question contient du contenu inapproprié.'
    ]);
    exit;
}


$bdd = getBD();

// nettoyage xss
$titre_clean = strip_tags($titre);
$contenu_clean = strip_tags($contenu);

// insertion
$stmt = $bdd->prepare("
    INSERT INTO questions (id_client, id_art, titre, contenu, date_creation) 
    VALUES (?, ?, ?, ?, NOW())
");

if ($stmt->execute([$id_client, $id_art, $titre_clean, $contenu_clean])) {
    echo json_encode([
        'success' => true,
        'message' => 'Votre question a été publiée avec succés!'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la publication'
    ]);
}


?>