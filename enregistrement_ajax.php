<?php
session_start();

require 'bd.php';
require 'csrf.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once 'stripe.php';

header('Content-Type: application/json; charset=utf-8');

requirePostMethod();
requireCsrfToken();

function cleanInput($data) {
    return strip_tags(trim($data));
}

$nom = isset($_POST['n']) ? cleanInput($_POST['n']) : '';
$prenom = isset($_POST['p']) ? cleanInput($_POST['p']) : '';
$adresse = isset($_POST['adr']) ? cleanInput($_POST['adr']) : '';
$numero = isset($_POST['num']) ? cleanInput($_POST['num']) : '';
$email = isset($_POST['mail']) ? filter_var(trim($_POST['mail']), FILTER_SANITIZE_EMAIL) : '';
$mdp1 = isset($_POST['mdp1']) ? $_POST['mdp1'] : '';
$mdp2 = isset($_POST['mdp2']) ? $_POST['mdp2'] : '';

// validation
if (empty($nom) || empty($prenom) || empty($adresse) || empty($numero) || empty($email) || empty($mdp1) || empty($mdp2)) {
    echo json_encode([
        'success' => false,
        'message' => 'Tous les champs sont obligatoires.'
    ]);
    exit();
}

if (strlen($nom) > 100 || strlen($prenom) > 100) {
    echo json_encode([
        'success' => false,
        'message' => 'Le nom et prenom ne peuvent pas depasser 100 caracteres.'
    ]);
    exit();
}

if (strlen($adresse) > 255) {
    echo json_encode([
        'success' => false,
        'message' => 'L\'adresse ne peut pas depasser 255 caracteres.'
    ]);
    exit();
}

if (!preg_match('/^[0-9\s\+\-\(\)\.]{8,20}$/', $numero)) {
    echo json_encode([
        'success' => false,
        'message' => 'Format de numero de telephone invalide.'
    ]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Format d\'email invalide.'
    ]);
    exit();
}

if (strlen($email) > 255) {
    echo json_encode([
        'success' => false,
        'message' => 'L\'adresse email est trop longue.'
    ]);
    exit();
}

if ($mdp1 !== $mdp2) {
    echo json_encode([
        'success' => false,
        'message' => 'Les mots de passe ne correspondent pas.'
    ]);
    exit();
}

if (strlen($mdp1) < 8 || strlen($mdp1) > 255) {
    echo json_encode([
        'success' => false,
        'message' => 'Le mot de passe doit contenir entre 8 et 255 caracteres.'
    ]);
    exit();
}

$hasLetter = preg_match('/[a-zA-Z]/', $mdp1);
$hasNumber = preg_match('/[0-9]/', $mdp1);
$hasSpecial = preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $mdp1);

if (!$hasLetter || !$hasNumber || !$hasSpecial) {
    echo json_encode([
        'success' => false,
        'message' => 'Le mot de passe doit contenir au moins 1 lettre, 1 chiffre et 1 caractere special.'
    ]);
    exit();
}


$bdd = getBD();

// verifica email existente
$stmt = $bdd->prepare("SELECT COUNT(*) as count FROM client WHERE mail = :mail");
$stmt->bindParam(':mail', $email, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result['count'] > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Cet email est deja utilise.'
    ]);
    exit();
}

// creation client stripe avant insertion bd 

    $stripe_customer = $stripe->customers->create([
        'email' => $email,
        'name' => $prenom . ' ' . $nom,
        'phone' => $numero,
        'address' => [
            'line1' => $adresse,
            'country' => 'FR',
        ],
        'metadata' => [
            'source' => 'sparkup_website',
            'nom' => $nom,
            'prenom' => $prenom
        ]
    ]);
    
    $stripe_customer_id = $stripe_customer->id;
    error_log("Client Stripe cree avec succes : " . $stripe_customer_id);
    


// hash pswrd
$mdp_crypte = password_hash($mdp1, PASSWORD_DEFAULT);

// insertion bd, avec id stripe
$stmt = $bdd->prepare("INSERT INTO client (nom, prenom, adresse, numero, mail, mdp, id_stripe) 
                        VALUES (:nom, :prenom, :adresse, :numero, :mail, :mdp, :id_stripe)");

$stmt->bindParam(':nom', $nom, PDO::PARAM_STR);
$stmt->bindParam(':prenom', $prenom, PDO::PARAM_STR);
$stmt->bindParam(':adresse', $adresse, PDO::PARAM_STR);
$stmt->bindParam(':numero', $numero, PDO::PARAM_STR);
$stmt->bindParam(':mail', $email, PDO::PARAM_STR);
$stmt->bindParam(':mdp', $mdp_crypte, PDO::PARAM_STR);
$stmt->bindParam(':id_stripe', $stripe_customer_id, PDO::PARAM_STR);

$stmt->execute();

$id_client = $bdd->lastInsertId();

// recuperation client crée avec id stripe
$stmt = $bdd->prepare("SELECT id_client, nom, prenom, adresse, numero, mail, id_stripe
                        FROM client 
                        WHERE id_client = :id_client");
$stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if ($client) {
    // sauvgarde en session
    $_SESSION['client'] = $client;
    
    // regenerate token x security
    session_regenerate_id(true);
    regenerateCsrfToken();
    
    error_log("utilisateu creé : ID " . $id_client . " - Stripe ID " . $stripe_customer_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Compte cree avec succes !',
        'redirect' => 'index.php'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la recuperation des informations du compte.'
    ]);
}

?>