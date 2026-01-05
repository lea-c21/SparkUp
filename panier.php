<?php
session_start();
require 'csrf.php';

if (!isset($_SESSION['client'])) {
    header('Location: connexion.php');
    exit();
}

require 'bd.php';
$bdd = getBD();
?>

<!DOCTYPE html>
<html>
<head>
<title>SparkUp - Mon Panier</title>
<meta http-equiv="Content-Type" 
content="text/html; charset=UTF-8" />

<link rel="stylesheet" href="styles/style1.css" type="text/css" media="screen" />
<script src="styles/interactions.js"></script>

</head>
<body>

<h1 id="titre">SparkUp !</h1> <br/>
<h2>Mon Panier</h2>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error">
        <?php 
        echo htmlspecialchars($_SESSION['error_message']); 
        unset($_SESSION['error_message']);
        ?>
    </div>
<?php endif; ?>

<?php
//panier vide?
if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    echo "<p>Votre panier ne contient aucun article.</p>";
} else {
    echo '<table id="table">';
    echo '<tr>';
    echo '<th>ID Article</th>';
    echo '<th>Nom</th>';
    echo '<th>Prix unitaire</th>';
    echo '<th>Quantité</th>';
    echo '<th>Prix total</th>';
    echo '</tr>';
    
    $montant_total = 0;
    
    //percorro tutto panier
    foreach ($_SESSION['panier'] as $item) {
        $id_article = $item['id_article'];
        $quantite = $item['quantite'];
        
        $stmt = $bdd->prepare('SELECT * FROM Articles WHERE id_art = :id_art');
        $stmt->execute([':id_art' => $id_article]);
        $article = $stmt->fetch();
        
        if ($article) {
            $nom = ($article['nom']);
            $prix_unitaire = ($article['prix']);
            $prix_total = $prix_unitaire * $quantite;
            
            $montant_total += $prix_total;
            
            echo '<tr>';
            echo '<td>' . ($id_article) . '</td>';
            echo '<td>' . $nom . '</td>';
            echo '<td>' . number_format($prix_unitaire, 2, ',', ' ') . ' €</td>';
            echo '<td>' . ($quantite) . '</td>';
            echo '<td>' . number_format($prix_total, 2, ',', ' ') . ' €</td>';
            echo '</tr>';
        }
    }
    
    echo '<tr>';
    echo '<td colspan="4" style="text-align: right; font-weight: bold;">Montant total de la commande:</td>';
    echo '<td style="font-weight: bold; color: #ffb400;">' . number_format($montant_total, 2, ',', ' ') . ' €</td>';
    echo '</tr>';
    
    echo '</table>';
    
    echo '<div style="text-align: center;">';
    echo '<a href="commande.php" class="btn-commander">Passer la commande</a>';
    echo '</div>';
}
?>

<p><a id="retour" href="index.php">Retour</a></p>
</body>
</html>