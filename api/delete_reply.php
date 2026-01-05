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
$id_reponse = isset($_POST['id_reponse']) ? intval($_POST['id_reponse']) : 0;

if ($id_reponse <= 0) {
    echo json_encode(['success' => false, 'message' => 'Réponse invalide']);
    exit;
}


$bdd = getBD();

// reply de l'utilisateur?
$stmt = $bdd->prepare("SELECT id_client FROM reponses WHERE id_reponse = ?");
$stmt->execute([$id_reponse]);
$reponse = $stmt->fetch();

if (!$reponse) {
    echo json_encode(['success' => false, 'message' => 'Réponse non trouvée']);
    exit;
}

if ($reponse['id_client'] != $id_client) {
    echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer cette réponse']);
    exit;
}

// suppression de toutes les reponses
deleteReplyRecursive($bdd, $id_reponse);

// delete reponse
$stmt = $bdd->prepare("DELETE FROM reponses WHERE id_reponse = ?");

if ($stmt->execute([$id_reponse])) {
    echo json_encode([
        'success' => true,
        'message' => 'Réponse supprimée avec succès'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la suppression'
    ]);
}
    


//suppression e toutes les reponses
function deleteReplyRecursive($bdd, $parent_id) {
    // Trova tutte le risposte figlie
    $stmt = $bdd->prepare("SELECT id_reponse FROM reponses WHERE id_parent = ?");
    $stmt->execute([$parent_id]);
    $children = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // elimina ricorsivamente ogni child
    foreach ($children as $child_id) {
        deleteReplyRecursive($bdd, $child_id);
        
        $stmt = $bdd->prepare("DELETE FROM reponses WHERE id_reponse = ?");
        $stmt->execute([$child_id]);
    }
}
?>