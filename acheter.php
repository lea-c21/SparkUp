<?php
session_start();

require_once __DIR__ . '/vendor/autoload.php';
require_once 'stripe.php';
require_once 'csrf.php';

if (!isset($_SESSION['client'])) {
    header('Location: connexion.php');
    exit();
}

if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    header('Location: index.php');
    exit();
}

// recup session_id 
$session_id = isset($_GET['session_id']) ? $_GET['session_id'] : null;

if (!$session_id) {
    $_SESSION['error_message'] = "Session de paiement invalide";
    header('Location: panier.php');
    exit();
}

require 'bd.php';
$bdd = getBD();

$id_client = $_SESSION['client']['id_client'];


// verification status paiement Stripe
$checkout_session = $stripe->checkout->sessions->retrieve($session_id);

// debug
error_log("=== DEBUG ACHETER.PHP ===");
error_log("Session ID: " . $session_id);
error_log("Payment Status: " . $checkout_session->payment_status);
error_log("Client ID: " . $id_client);

// paiement effectué?
if ($checkout_session->payment_status !== 'paid') {
    $_SESSION['error_message'] = "Le paiement n'a pas été confirmé";
    error_log("Paiement non confirmé pour session: " . $session_id);
    header('Location: commande.php');
    exit();
}

// session gia trattata?
$stmt_check_session = $bdd->prepare("SELECT COUNT(*) as count FROM commandes WHERE stripe_session_id = :session_id");
$stmt_check_session->bindParam(':session_id', $session_id, PDO::PARAM_STR);
$stmt_check_session->execute();
$result = $stmt_check_session->fetch();

if ($result['count'] > 0) {
    // session gia trattata
    error_log("Session già processata: " . $session_id);
    unset($_SESSION['panier']);
    regenerateCsrfToken();
} else {
    // traitement della commanda
    error_log("Inizio trattamento commanda per session: " . $session_id);
    $bdd->beginTransaction();
    
    foreach ($_SESSION['panier'] as $item) {
        $id_article = $item['id_article'];
        $quantite = $item['quantite'];
        
        error_log("Elaborazione articolo ID: $id_article, Quantità: $quantite");
        
        // verification stock
        $stmt_check = $bdd->prepare("SELECT quantite FROM articles WHERE id_art = :id_art FOR UPDATE");
        $stmt_check->bindParam(':id_art', $id_article, PDO::PARAM_INT);
        $stmt_check->execute();
        $article = $stmt_check->fetch();
        
        if (!$article) {
            $bdd->rollBack();
            $_SESSION['error_message'] = "Article introuvable (ID: $id_article)";
            error_log("Article non trouvé: $id_article");
            header('Location: panier.php');
            exit();
        }
        
        if ($article['quantite'] < $quantite) {
            $bdd->rollBack();
            $_SESSION['error_message'] = "Stock insuffisant pour l'article ID: $id_article";
            error_log("Stock insuffisant pour article $id_article. Stock: {$article['quantite']}, Demandé: $quantite");
            header('Location: panier.php');
            exit();
        }
        
        // insertion commanda session stripe
        $stmt = $bdd->prepare("INSERT INTO commandes (id_art, id_client, quantite, envoi, stripe_session_id, stripe_payment_intent) 
                                VALUES (:id_art, :id_client, :quantite, 0, :stripe_session_id, :stripe_payment_intent)");
        $stmt->bindParam(':id_art', $id_article, PDO::PARAM_INT);
        $stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
        $stmt->bindParam(':quantite', $quantite, PDO::PARAM_INT);
        $stmt->bindParam(':stripe_session_id', $session_id, PDO::PARAM_STR);
        $stripe_payment_intent = $checkout_session->payment_intent;
        $stmt->bindParam(':stripe_payment_intent', $stripe_payment_intent, PDO::PARAM_STR);
        
        if (!$stmt->execute()) {
            $bdd->rollBack();
            error_log("Errore inserimento commanda: " . print_r($stmt->errorInfo(), true));
            $_SESSION['error_message'] = "Erreur lors de l'enregistrement de la commande";
            header('Location: panier.php');
            exit();
        }
        
        error_log("Commanda inserita con successo per articolo: $id_article");
        
        // mise a jour stock
        $stmt_update = $bdd->prepare("UPDATE articles 
                                        SET quantite = quantite - :quantite 
                                        WHERE id_art = :id_art");
        $stmt_update->bindParam(':quantite', $quantite, PDO::PARAM_INT);
        $stmt_update->bindParam(':id_art', $id_article, PDO::PARAM_INT);
        
        if (!$stmt_update->execute()) {
            $bdd->rollBack();
            error_log("Errore aggiornamento stock: " . print_r($stmt_update->errorInfo(), true));
            $_SESSION['error_message'] = "Erreur lors de la mise à jour du stock";
            header('Location: panier.php');
            exit();
        }
        
        error_log("Stock aggiornato per articolo: $id_article");
    }
    
    $bdd->commit();
    error_log("Commanda completata con successo!");
    
    // vide panier  et regenration token
    unset($_SESSION['panier']);
    regenerateCsrfToken();
}


?>
<!DOCTYPE html>
<html>
<head>
<title>SparkUp - Acheter</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="styles/style1.css" type="text/css" media="screen" />
</head>
<body>

<h1 id="titre">SparkUp !</h1> <br/>
<h2>Commande validée !</h2>

<div class="confirmation-message">
    <h3>Votre commande a bien été enregistrée et payée.</h3>
    <p>Merci pour votre achat, <strong><?php echo ($_SESSION['client']['prenom']); ?></strong> !</p>
    <p>Vous recevrez un email de confirmation à l'adresse : <strong><?php echo ($_SESSION['client']['mail']); ?></strong></p>
    <p>Votre commande sera expédiée dans les plus brefs délais à l'adresse indiquée.</p>
</div>

<div style="text-align: center;">
    <p><a href="index.php">Retour à l'accueil</a></p>
    <p><a href="historique.php">Voir mes commandes</a></p>
</div>

</body>
</html>