<?php
session_start();
require 'csrf.php';

if (!isset($_SESSION['client'])) {
    header('Location: connexion.php');
    exit();
}

require 'bd.php';
$bdd = getBD();

$id_client = $_SESSION['client']['id_client'];
?>
<!DOCTYPE html>
<html>
<head>
<title>SparkUp - Historique des commandes</title>
<meta http-equiv="Content-Type" 
content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="styles/style1.css" type="text/css" media="screen" />
<script src="styles/interactions.js"></script>

</head>
<body>

<h1 id="titre">SparkUp !</h1> <br/>
<h2>Historique de vos commandes</h2>

<?php
$stmt = $bdd->prepare("SELECT c.id_commande, c.id_art, c.quantite, c.envoi, 
                              a.nom, a.prix 
                       FROM commandes c
                       INNER JOIN articles a ON c.id_art = a.id_art
                       WHERE c.id_client = :id_client
                       ORDER BY c.id_commande DESC");
$stmt->bindParam(':id_client', $id_client);
$stmt->execute();

$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($commandes)) {
    echo "<p>Vous n'avez pas encore passé de commande.</p>";
} else {
    echo '<table id="table">';
    echo '<tr>';
    echo '<th>ID Commande</th>';
    echo '<th>ID Article</th>';
    echo '<th>Nom Article</th>';
    echo '<th>Quantité</th>';
    echo '<th>Prix unitaire</th>';
    echo '<th>Prix total</th>';
    echo '<th>Statut d\'envoi</th>';
    echo '</tr>';
    
    $montant_total_global = 0;
    
    foreach ($commandes as $commande) {
        $prix_unitaire = floatval($commande['prix']);
        $quantite = intval($commande['quantite']);
        $prix_total = $prix_unitaire * $quantite;
        $montant_total_global += $prix_total;
        
        echo '<tr>';
        echo '<td>' . ($commande['id_commande']) . '</td>';
        echo '<td>' . ($commande['id_art']) . '</td>';
        echo '<td>' . ($commande['nom']) . '</td>';
        echo '<td>' . ($quantite) . '</td>';
        echo '<td>' . number_format($prix_unitaire, 2, ',', ' ') . ' €</td>';
        echo '<td>' . number_format($prix_total, 2, ',', ' ') . ' €</td>';
        echo '<td>';
        
        // statut d'envoi
        if ($commande['envoi'] == 1) {
            echo '<span class="statut-envoi envoye">Envoyé</span>';
        } else {
            echo '<span class="statut-envoi en-cours">En cours</span>';
        }
        
        echo '</td>';
        echo '</tr>';
    }
    
    echo '<tr>';
    echo '<td colspan="5" style="text-align: right; font-weight: bold;">Montant total de toutes vos commandes:</td>';
    echo '<td style="font-weight: bold; color: #ffb400;">' . number_format($montant_total_global, 2, ',', ' ') . ' €</td>';
    echo '<td></td>';
    echo '</tr>';
    
    echo '</table>';
    
    $nb_commandes = count($commandes);
    $nb_envoyees = 0;
    $nb_en_cours = 0;
    
    foreach ($commandes as $commande) {
        if ($commande['envoi'] == 1) {
            $nb_envoyees++;
        } else {
            $nb_en_cours++;
        }
    }
    
    echo '<div class="commande-info">';
    echo '<h3>Statistiques</h3>';
    echo '<p>Nombre total de commandes : <strong>' . $nb_commandes . '</strong></p>';
    echo '<p>Commandes envoyées : <strong>' . $nb_envoyees . '</strong></p>';
    echo '<p>Commandes en cours : <strong>' . $nb_en_cours . '</strong></p>';
    echo '</div>';
}
?>

<p><a id="retour" href="index.php">Retour</a></p>
</body>
</html>