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

// Vcomment de l'utilisateur?
$stmt = $bdd->prepare("SELECT id_client FROM commentaires WHERE id_commentaire = ?");
$stmt->execute([$id_commentaire]);
$comment = $stmt->fetch();

if (!$comment) {
    echo json_encode(['success' => false, 'message' => 'Commentaire non trouvé']);
    exit;
}

if ($comment['id_client'] != $id_client) {
    echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer ce commentaire']);
    exit;
}

// suppression likes
$stmt = $bdd->prepare("DELETE FROM commentaire_likes WHERE id_commentaire = ?");
$stmt->execute([$id_commentaire]);

// suppression comment
$stmt = $bdd->prepare("DELETE FROM commentaires WHERE id_commentaire = ?");

if ($stmt->execute([$id_commentaire])) {
    echo json_encode([
        'success' => true,
        'message' => 'Commentaire supprimé avec succès'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression'
    ]);
}
    
?>