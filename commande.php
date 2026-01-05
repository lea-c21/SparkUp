<?php
session_start();
require 'csrf.php';

if (!isset($_SESSION['client'])) {
    header('Location: connexion.php');
    exit();
}

if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    header('Location: panier.php');
    exit();
}

require 'bd.php';
$bdd = getBD();

// message annulation paiement
$payment_cancelled = isset($_GET['payment']) && $_GET['payment'] === 'cancelled';
?>

<!DOCTYPE html>
<html>
<head>
<title>SparkUp - Commande</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<link rel="stylesheet" href="styles/style1.css" type="text/css" media="screen" />
<script src="styles/interactions.js"></script>

</head>
<body>

<h1 id="titre">SparkUp !</h1> <br/>
<h2>Récapitulatif de votre commande :</h2>

<?php if ($payment_cancelled): ?>
    <div class="alert alert-error">
        <p>Le paiement a été annulé. Vous pouvez réessayer quand vous le souhaitez.</p>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error">
        <?php 
        echo ($_SESSION['error_message']); 
        unset($_SESSION['error_message']);
        ?>
    </div>
<?php endif; ?>

<table id="table">
<tr>
    <th>ID Article</th>
    <th>Nom</th>
    <th>Prix unitaire</th>
    <th>Quantité</th>
    <th>Prix total</th>
</tr>

<?php
$montant_total = 0;
//table liste elements dans panier
foreach ($_SESSION['panier'] as $item) {
    $id_article = $item['id_article'];
    $quantite = $item['quantite'];
    
    $stmt = $bdd->prepare('SELECT * FROM Articles WHERE id_art = :id_art');
    $stmt->execute([':id_art' => $id_article]);
    $article = $stmt->fetch();
    
    if ($article) {
        $nom = ($article['nom']);
        $prix_unitaire = floatval($article['prix']);
        $prix_total = $prix_unitaire * $quantite;
        
        $montant_total += $prix_total;
        
        echo '<tr>';
        echo '<td>' . ($id_article) . '</td>';
        echo '<td>' . $nom . '</td>';
        echo '<td>' .  number_format($prix_unitaire, 2, ',', ' ') . ' €</td>';
        echo '<td>' . ($quantite) . '</td>';
        echo '<td>' .  number_format($prix_total, 2, ',', ' ') . ' €</td>';
        echo '</tr>';
    }
}

echo '<tr>';
echo '<td colspan="4" style="text-align: right; font-weight: bold;">Montant total de la commande:</td>';
echo '<td style="font-weight: bold; color: #ffb400;">' .  number_format($montant_total, 2, ',', ' ') . ' €</td>';
echo '</tr>';
?>

</table>

<div class="commande-info">
    <h3>Montant de votre commande : <?php echo number_format($montant_total, 2, ',', ' '); ?> €</h3>
    
    <h3>La commande sera expédiée à l'adresse suivante :</h3>
    <div class="adresse-livraison">
        <p><strong><?php echo ($_SESSION['client']['nom']); ?></strong></p>
        <p><strong><?php echo ($_SESSION['client']['prenom']); ?></strong></p>
        <p><?php echo ($_SESSION['client']['adresse']); ?></p>
    </div>
</div>

<div class="commande-actions">
    <!-- Formulaire vers paiement.php -->
    <form action="paiement.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo (generateCsrfToken()); ?>">
        <input type="submit" value="Procéder au paiement" >
    </form>
    <p><a id="retour" href="panier.php">Retour</a></p>
</div>

</body>
</html>