<?php
session_start();
require 'bd.php';
require 'csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : (isset($_GET['email']) ? trim($_GET['email']) : '');

if (empty($email)) {
    echo json_encode(['existe' => false, 'erreur' => 'Email vide']);
    exit();
}

//email dejà existente?
$bdd = getBD();

$stmt = $bdd->prepare("SELECT COUNT(*) as count FROM client WHERE mail = :mail");
$stmt->bindParam(':mail', $email, PDO::PARAM_STR);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);

$existe = ($result['count'] > 0);

echo json_encode(['existe' => $existe]);
    

?>