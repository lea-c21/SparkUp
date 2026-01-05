<?php
session_start();

require_once __DIR__ . '/vendor/autoload.php';
require_once 'stripe.php';
require_once 'csrf.php';
require 'bd.php';

requirePostMethod();
requireCsrfToken();

// verifica session et panier
if (!isset($_SESSION['client'])) {
    header('Location: connexion.php');
    exit;
}

if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    $_SESSION['error_message'] = "Votre panier est vide. Impossible de procéder au paiement.";
    header('Location: panier.php');
    exit;
}

// recuperation id stripe de l'utilisateur connécté
$stripe_customer_id = $_SESSION['client']['id_stripe'] ?? null;

if (empty($stripe_customer_id)) {
    $_SESSION['error_message'] = "Erreur: ID client Stripe manquant. Veuillez vous reconnecter.";
    header('Location: commande.php');
    exit;
}

// prepara line items
$bdd = getBD();
$line_items = [];

// contruction urls
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

//  redirections
$success_url = $base_url . '/Carminati/acheter.php?session_id={CHECKOUT_SESSION_ID}';
$cancel_url = $base_url . '/Carminati/commande.php?payment=cancelled';


    // verification stock
    $bdd->beginTransaction();
    
    foreach ($_SESSION['panier'] as $item) {
        $id_article = $item['id_article'];
        $quantite = $item['quantite'];
        
        // recup article avec id stripe
        $stmt = $bdd->prepare('SELECT nom, prix, quantite, id_stripe FROM articles WHERE id_art = :id_art FOR UPDATE');
        $stmt->execute([':id_art' => $id_article]);
        $article = $stmt->fetch();
        
        if (!$article) {
            $bdd->rollBack();
            $_SESSION['error_message'] = "Article introuvable (ID: $id_article).";
            header('Location: commande.php');
            exit;
        }
        
        // verification du stock
        if ($article['quantite'] < $quantite) {
            $bdd->rollBack();
            $_SESSION['error_message'] = "Stock insuffisant pour l'article: " . ($article['nom']);
            header('Location: commande.php');
            exit;
        }
        
        // verification article a id stripe
        if (empty($article['id_stripe'])) {
            $bdd->rollBack();
            $_SESSION['error_message'] = "L'article '" . ($article['nom']) . "' n'a pas d'ID Stripe configuré.";
            error_log("Article sans ID Stripe: ID " . $id_article . " - " . $article['nom']);
            header('Location: commande.php');
            exit;
        }

        // ajout pour gerer prod_
        if (strpos($article['id_stripe'], 'prod_') === 0) {
            //  prix temporaire pour ce produit
            
            $price = $stripe->prices->create([
                'product' => $article['id_stripe'],
                'unit_amount' => $article['prix'] * 100, // en centimes
                'currency' => 'eur',
            ]);
            $price_id = $price->id;
            error_log("Création d'un prix temporaire pour le produit {$article['nom']} : $price_id");
            
        } else {
            $price_id = $article['id_stripe'];
        }

        $line_items[] = [
            'price' => $price_id, 
            'quantity' => $quantite,
        ];
    }
    
    // stock verifié
    $bdd->rollBack();

    // creation session checkout 
    $checkout_session = $stripe->checkout->sessions->create([
        'customer' => $stripe_customer_id, // ID USER
        'success_url' => $success_url, // redirection si OK
        'cancel_url' => $cancel_url, // redirection si PAS OK
        'mode' => 'payment', // mode pour paiement unique
        'automatic_tax' => ['enabled' => false],
        'line_items' => $line_items, // $TOTO
        'locale' => 'fr',
    ]);

    error_log("Session Stripe créée: " . $checkout_session->id . " pour le client " . $stripe_customer_id);


// redirection page stripe
header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);
exit;