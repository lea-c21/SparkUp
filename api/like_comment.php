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
$id_commentaire = isset($_POST['id_commentaire']) ? intval($_POST['id_commentaire']) : 0;

if ($id_commentaire <= 0) {
    echo json_encode(['success' => false, 'message' => 'Commentaire invalide']);
    exit;
}


$bdd = getBD();

// dejà liké?
$stmt = $bdd->prepare("SELECT COUNT(*) FROM commentaire_likes WHERE id_commentaire = ? AND id_client = ?");
$stmt->execute([$id_commentaire, $id_client]);
$gia_liked = $stmt->fetchColumn() > 0;

if ($gia_liked) {
    // si deja liké, suppression like
    $stmt = $bdd->prepare("DELETE FROM commentaire_likes WHERE id_commentaire = ? AND id_client = ?");
    $stmt->execute([$id_commentaire, $id_client]);
    $message = 'Like retiré';
} else {
    // ajout like
    $stmt = $bdd->prepare("INSERT INTO commentaire_likes (id_commentaire, id_client, date_like) VALUES (?, ?, NOW())");
    $stmt->execute([$id_commentaire, $id_client]);
    $message = 'Like ajouté';
}

echo json_encode([
    'success' => true,
    'message' => $message
]);
    
?>