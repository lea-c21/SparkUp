<?php
session_start();
require_once '../bd.php';
require_once '../csrf.php';
require_once '../filer_config.php';

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
$contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';
$note = isset($_POST['note']) ? intval($_POST['note']) : 0;

// validations commentaire
if ($id_art <= 0) {
    echo json_encode(['success' => false, 'message' => 'Article invalide']);
    exit;
}

if (empty($contenu)) {
    echo json_encode(['success' => false, 'message' => 'Le commentaire ne peut pas être vide']);
    exit;
}

if ($note < 1 || $note > 5) {
    echo json_encode(['success' => false, 'message' => 'La note doit être entre 1 et 5']);
    exit;
}

// filtrage scoremap
$filter_result = filterMessage($contenu);

if ($filter_result['is_offensive']) {
    error_log("Commentaire bloqué (méthode: {$filter_result['method']}) de l'utilisateur $id_client");
    echo json_encode([
        'success' => false,
        'message' => 'Votre commentaire contient du contenu inapproprié.'
    ]);
    exit;
}

$bdd = getBD();

// verification produit acheté
$stmt = $bdd->prepare("SELECT COUNT(*) FROM commandes WHERE id_client = ? AND id_art = ?");
$stmt->execute([$id_client, $id_art]);

if ($stmt->fetchColumn() == 0) {
    echo json_encode(['success' => false, 'message' => 'Vous devez acheter ce produit pour laisser un avis']);
    exit;
}

// verification comment deja existant
$stmt = $bdd->prepare("SELECT COUNT(*) FROM commentaires WHERE id_client = ? AND id_art = ?");
$stmt->execute([$id_client, $id_art]);

if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Vous avez déjà  laissé un avis pour ce produit']);
    exit;
}

// nettoyage xxs
$contenu_clean = strip_tags($contenu);

// insertion comment
$stmt = $bdd->prepare("
    INSERT INTO commentaires (id_client, id_art, contenu, note, date_creation) 
    VALUES (?, ?, ?, ?, NOW())
");

if ($stmt->execute([$id_client, $id_art, $contenu_clean, $note])) {
    echo json_encode([
        'success' => true,
        'message' => 'Votre avis a été publié avec succés!'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la publication'
    ]);
}
    
?>