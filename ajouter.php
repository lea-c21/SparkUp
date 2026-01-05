<?php
session_start();

require 'csrf.php';

requirePostMethod();
requireCsrfToken();

if (!isset($_SESSION['client'])) {
    header('Location: connexion.php');
    exit();
}

$id_article = isset($_POST['id_article']) ? intval($_POST['id_article']) : 0;
$quantite = isset($_POST['quantite']) ? intval($_POST['quantite']) : 0;

if ($id_article <= 0 || $quantite <= 0 || $quantite > 1000) {
    $_SESSION['error_message'] = "Données invalides";
    header('Location: index.php');
    exit();
}

require 'bd.php';
$bdd = getBD();

$stmt = $bdd->prepare("SELECT quantite FROM articles WHERE id_art = :id_art");
$stmt->bindParam(':id_art', $id_article, PDO::PARAM_INT);
$stmt->execute();
$article = $stmt->fetch();

if (!$article) {
    $_SESSION['error_message'] = "Article introuvable";
    header('Location: index.php');
    exit();
}

if ($article['quantite'] < $quantite) {
    $_SESSION['error_message'] = "Stock insuffisant";
    header('Location: index.php');
    exit();
}

$article_a_ajouter = array(
    'id_article' => $id_article,
    'quantite' => $quantite
);

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = array($article_a_ajouter);
} else {
    $article_existe = false;
    
    for ($i = 0; $i < count($_SESSION['panier']); $i++) {
        if ($_SESSION['panier'][$i]['id_article'] == $id_article) {
            $_SESSION['panier'][$i]['quantite'] += $quantite;
            $article_existe = true;
            break;
        }
    }
    
    if (!$article_existe) {
        array_push($_SESSION['panier'], $article_a_ajouter);
    }
}

$_SESSION['success_message'] = "Article ajouté au panier";
header('Location: index.php');
exit();
?>