<?php
session_start();
require 'csrf.php'; 
?>

<!DOCTYPE html>
<html>
<head>
<title>SparkUp</title>

<meta http-equiv="Content-Type" 
content="text/html; charset=UTF-8" />

<link rel="stylesheet" href="styles/style1.css" type="text/css" media="screen" />
<script src="styles/interactions.js"></script>

</head>
<body>

<h1 id="titre"> SparkUp !</h1> <br/>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?php 
        echo ($_SESSION['success_message']); 
        unset($_SESSION['success_message']);
        ?>
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

<?php if (isset($_SESSION['client'])): ?>
    <h2>Bonjour <br/> <?php echo ($_SESSION['client']['prenom']) . ' ' . ($_SESSION['client']['nom']); ?></h2>
    <p>
        <a href="panier.php">Voir mon panier</a> |
        <a href="historique.php">Historique des commandes</a> |		
        <a href="deconnexion.php?token=<?php echo urlencode(generateCsrfToken()); ?>">Se déconnecter</a>
    </p>
<?php else: ?>
    <p>
        <a href="nouveau.php">Nouveau client</a> | 
        <a href="connexion.php">Se connecter</a>
    </p>
<?php endif; ?>

<p>Les articles :</p>

<table id="table.articles">
<tr> 
	<th>Nom Article</th>
	<th>ID</th>
	<th>Quantité en stock</th>
	<th>Prix</th>
</tr>

<?php
require 'bd.php';
$bdd = getBD();

// affichage par rating (media recenzione)
$query = $bdd->query("
    SELECT 
        a.*,
        COALESCE(AVG(c.note), 0) as note_moyenne,
        COUNT(c.id_commentaire) as nombre_avis
    FROM articles a
    LEFT JOIN commentaires c ON a.id_art = c.id_art
    GROUP BY a.id_art
    ORDER BY note_moyenne DESC, a.id_art DESC
");

while ($ligne = $query->fetch()) {
    echo "<tr>";
    echo "<td><a href='articles/article.php?id_art=" . ($ligne['id_art']) . "'>" . ($ligne['nom']) . "</a></td>";
    echo "<td>" . ($ligne['id_art']) . "</td>";
    echo "<td>" . ($ligne['quantite']) . "</td>";
    echo "<td>" . ($ligne['prix']) . " €</td>";
    echo "</tr>";
}

$query->closeCursor();
?>

</table>

<p> Pour plus d'informations: <a href="contact/contact.html">Contact</a></p>

<?php
if (isset($_SESSION['client'])) {
    include 'chat.php';
}
?>

</body>
</html>