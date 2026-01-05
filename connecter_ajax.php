<?php
session_start();

require 'bd.php';
require 'csrf.php';

requirePostMethod();
requireCsrfToken();

$mail = isset($_POST['mail']) ? filter_var(trim($_POST['mail']), FILTER_SANITIZE_EMAIL) : '';
$mdp = isset($_POST['mdp']) ? $_POST['mdp'] : '';

if (empty($mail) || empty($mdp)) {
    echo json_encode([
        'success' => false,
        'message' => 'L\'adresse email et le mot de passe sont obligatoires.'
    ]);
    exit();
}

if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Format d\'email invalide.'
    ]);
    exit();
}


$bd = getBD();

// recuperation avec le champ stripe_customer_id
$stmt = $bd->prepare("SELECT id_client, nom, prenom, adresse, numero, mail, mdp, id_stripe
                        FROM client 
                        WHERE mail = :mail");
$stmt->bindParam(':mail', $mail, PDO::PARAM_STR);
$stmt->execute();

$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client || !password_verify($mdp, $client['mdp'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email ou mot de passe incorrect.'
    ]);
    exit();
}

// suppr du mot de passe avant stockage en session
unset($client['mdp']);
$_SESSION['client'] = $client;

$_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
$_SESSION['last_activity'] = time();

session_regenerate_id(true);
regenerateCsrfToken();

echo json_encode([
    'success' => true,
    'message' => 'Connexion réussie !'
]);
    

?>