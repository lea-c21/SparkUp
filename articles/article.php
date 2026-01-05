<?php
session_start();
require_once '../csrf.php';
require_once '../bd.php';

if (!isset($_GET['id_art']) || !is_numeric($_GET['id_art'])) {
    header('Location: ../index.php');
    exit();
}

$id_art = intval($_GET['id_art']);
$bdd = getBD();

// recup article
$stmt = $bdd->prepare("SELECT * FROM articles WHERE id_art = :id_art");
$stmt->bindParam(':id_art', $id_art, PDO::PARAM_INT);
$stmt->execute();
$article = $stmt->fetch();

if (!$article) {
    header('Location: ../index.php');
    exit();
}

// calcul rating medio (coalesce rinvia 0 e non null se no recenzioni)
$stmt = $bdd->prepare("
    SELECT 
        COALESCE(AVG(note), 0) as note_moyenne,
        COUNT(*) as nombre_commentaires,
        SUM(CASE WHEN note = 5 THEN 1 ELSE 0 END) as etoiles_5,
        SUM(CASE WHEN note = 4 THEN 1 ELSE 0 END) as etoiles_4,
        SUM(CASE WHEN note = 3 THEN 1 ELSE 0 END) as etoiles_3,
        SUM(CASE WHEN note = 2 THEN 1 ELSE 0 END) as etoiles_2,
        SUM(CASE WHEN note = 1 THEN 1 ELSE 0 END) as etoiles_1
    FROM commentaires 
    WHERE id_art = :id_art
");
$stmt->execute([':id_art' => $id_art]);
$rating = $stmt->fetch();

// verification si article a ete acheté, si commande alors ok
$ha_acquistato = false;
if (isset($_SESSION['client'])) {
    $stmt = $bdd->prepare("SELECT COUNT(*) FROM commandes WHERE id_client = ? AND id_art = ?");
    $stmt->execute([$_SESSION['client']['id_client'], $id_art]);
    $ha_acquistato = $stmt->fetchColumn() > 0;
}

// commentaires recup
$stmt = $bdd->prepare("
    SELECT 
        c.*,
        cl.prenom,
        cl.nom,
        COALESCE(l.like_count, 0) as likes,
        CASE WHEN ul.id_like IS NOT NULL THEN 1 ELSE 0 END as user_liked
    FROM commentaires c
    INNER JOIN client cl ON c.id_client = cl.id_client
    LEFT JOIN (
        SELECT id_commentaire, COUNT(*) as like_count 
        FROM commentaire_likes 
        GROUP BY id_commentaire
    ) l ON c.id_commentaire = l.id_commentaire
    LEFT JOIN commentaire_likes ul ON c.id_commentaire = ul.id_commentaire 
        AND ul.id_client = ?
    WHERE c.id_art = ?
    ORDER BY likes DESC, c.date_creation DESC
");
$stmt->execute([
    isset($_SESSION['client']) ? $_SESSION['client']['id_client'] : 0,
    $id_art
]);
$commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// recupe questions (general)
$stmt = $bdd->prepare("
    SELECT q.*, cl.prenom, cl.nom, a.nom as produit_nom
    FROM questions q
    INNER JOIN client cl ON q.id_client = cl.id_client
    INNER JOIN articles a ON q.id_art = a.id_art
    ORDER BY q.date_creation DESC
");
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$csrf_token = generateCsrfToken();
?>

<!DOCTYPE html>
<html>
<head>
    <title>SparkUp - <?php echo ($article['nom']); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="../styles/style_article.css" type="text/css" media="screen" />
    
</head>
<body>
<h1 id="titre">SparkUp !</h1>

<?php
// affichage produit
echo "<h2>" . ($article['nom']) . "</h2>";

echo '<div class="product-container">';

// image
echo '<div class="product-image">';
echo "<img src='" . ($article['url_photo']) . "' alt='" . ($article['nom']) . "' />"; 
echo '</div>';

// description
echo '<div class="product-info">';

echo "<p><strong>Quantité en stock:</strong> " . ($article['quantite']) . "</p>";
echo "<p><strong>Prix:</strong> " . ($article['prix']) . " €</p>";

echo "<div class='description'>";
echo "<h3>Description</h3>";
echo "<p>" . nl2br(($article['description'])) . "</p>";
echo "</div>";

echo '</div>'; 

echo '<div class="bottom-section">';

// affichage rating
echo '<div class="product-rating-sidebar">';
if ($rating['nombre_commentaires'] > 0) {
    $note_moyenne = round($rating['note_moyenne'], 1);
    $stars_full = floor($note_moyenne);
    $stars_empty = 5 - $stars_full;
    
    echo '<h3 style="color: #ffa500; margin-bottom: 15px;">Avis clients</h3>';
    echo '<div class="product-rating">';
    echo '<div class="stars">';
    for ($i = 0; $i < $stars_full; $i++) echo '★';
    for ($i = 0; $i < $stars_empty; $i++) echo '☆';
    echo '</div>';
    echo "<div style='font-size: 18px;'><strong>$note_moyenne</strong> sur 5</div>";
    echo "<div style='color: rgba(255,255,255,0.7);'>({$rating['nombre_commentaires']} avis)</div>";
    echo '</div>';
    
    // distribuzione stelle 
    echo '<div class="rating-bars" style="margin-top: 20px;">';
    for ($i = 5; $i >= 1; $i--) {
        $count = $rating["etoiles_$i"] ?? 0;
        $percentage = ($count / $rating['nombre_commentaires']) * 100;
        echo '<div class="rating-bar">';
        echo "<span>$i ★</span>";
        echo '<div class="bar">';
        echo '<div class="bar-fill" style="width: ' . $percentage . '%"></div>';
        echo '</div>';
        echo "<span>$count</span>";
        echo '</div>';
    }
    echo '</div>';
} else {
    echo '<h3 style="color: #ffa500;">Aucun avis</h3>';
    echo '<p style="color: rgba(255,255,255,0.7);">Soyez le premier à donner votre avis!</p>';
}
echo '</div>'; 

// aggiunta panier
if (isset($_SESSION['client'])) {
    echo '<div class="panier-form">';
    echo '<h3>Ajouter au panier</h3>';
    echo '<form action="../ajouter.php" method="post">';
    echo '<input type="hidden" name="csrf_token" value="' . ($csrf_token) . '">';
    echo '<input type="hidden" name="id_article" value="' . $article['id_art'] . '">';
    
    echo '<div class="form-group">';
    echo '<label for="quantite">Nombre d\'exemplaires :</label>';
    echo '<input type="number" id="quantite" name="quantite" min="1" max="' . $article['quantite'] . '" value="1" required>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<input type="submit" value="Ajouter à votre panier" class="enabled">';
    echo '</div>';
    
    echo '</form>';
    echo '</div>'; 
    
    echo '</div>'; 
    echo '</div>'; 
} else { //se non connesso allora messaggio
    echo '</div>'; 
    echo '</div>'; 
    
    echo '<div class="login-message">';
    echo '<p>Connectez-vous pour ajouter cet article à votre panier.</p>';
    echo '<p><a href="../connexion.php">Se connecter</a> ou <a href="../nouveau.php">Créer un compte</a></p>';
    echo '</div>';
}
?>

<!-- section commentaires -->
<div class="section" id="commentaires">
    <h2 class="section-title">Commentaires et avis</h2>
    
    <?php if (isset($_SESSION['client']) && $ha_acquistato): ?>
        <!-- nouveau commentaire -->
        <div class="comment-form">
            <h3>Laisser un avis</h3>
            <div id="comment-error" class="error-message"></div>
            <div id="comment-success" class="success-message"></div>
            
            <form id="commentForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="id_art" value="<?php echo $id_art; ?>">
                

                <div class="form-group">
                    <label>Votre note *</label>
                    <div class="star-rating" id="starRating">
                        <span data-value="1">★</span>
                        <span data-value="2">★</span>
                        <span data-value="3">★</span>
                        <span data-value="4">★</span>
                        <span data-value="5">★</span>
                    </div>
                    <input type="hidden" name="note" id="noteInput" required>
                </div>
                
                <div class="form-group">
                    <label for="commentaire">Votre commentaire *</label>
                    <textarea name="contenu" id="commentaire" rows="4" required 
                              placeholder="Partagez votre expérience avec ce produit..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Publier l'avis</button>
            </form>
        </div>
    <?php elseif (!isset($_SESSION['client'])): ?>
        <p><a href="../connexion.php">Connectez-vous</a> pour laisser un avis.</p>
    <?php else: ?>
        <p>Vous devez acheter ce produit pour laisser un avis.</p>
    <?php endif; ?>
    
    <!-- commentaires precedentes -->
    <div id="commentsList">
        <?php foreach ($commentaires as $comment): ?>
            <div class="comment-card" data-id="<?php echo $comment['id_commentaire']; ?>">
                <div class="comment-header">
                    <div class="author-info">
                        <strong><?php echo ($comment['prenom']); ?></strong>
                        <div class="stars">
                            <?php 
                            for ($i = 0; $i < $comment['note']; $i++) echo '★';
                            for ($i = $comment['note']; $i < 5; $i++) echo '☆';
                            ?>
                        </div>
                    </div>
                    <small><?php echo date('d/m/Y H:i', strtotime($comment['date_creation'])); ?></small>
                </div>
                
                <p><?php echo nl2br(($comment['contenu'])); ?></p>
                
                <div class="comment-actions">
                    <?php if (isset($_SESSION['client'])): ?>
                        <button class="btn btn-like <?php echo $comment['user_liked'] ? 'liked' : ''; ?>" 
                                onclick="likeComment(<?php echo $comment['id_commentaire']; ?>)">
                                 <?php echo $comment['likes']; ?> Utile
                        </button>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['client']) && $_SESSION['client']['id_client'] == $comment['id_client']): ?>
                        <button class="btn btn-delete" 
                                onclick="deleteComment(<?php echo $comment['id_commentaire']; ?>)">
                            Supprimer
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($commentaires)): ?>
            <p>Aucun commentaire pour le moment. Soyez le premier à donner votre avis !</p>
        <?php endif; ?>
    </div>
</div>

<!-- questions (visible sut tout produit) -->
<div class="section" id="questions">
    <h2 class="section-title">Questions et réponses</h2>
    <p style="color: rgba(255,255,255,0.7); margin-bottom: 20px;">
        <em>Posez vos questions ici!</em>
    </p>
    
    <!-- barre recherche -->
    <div class="search-box">
        <input type="text" id="searchQuestions" 
               placeholder=" Rechercher une question existante...">
    </div>
    
    <?php if (isset($_SESSION['client'])): ?>
        <!-- nouvelle question -->
        <div class="question-form">
            <h3>Poser une question</h3>
            <div id="question-error" class="error-message"></div>
            <div id="question-success" class="success-message"></div>
            
            <form id="questionForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="id_art" value="<?php echo $id_art; ?>">
                
                <div class="form-group">
                    <label for="titre">Titre de la question </label>
                    <input type="text" name="titre" required 
                           placeholder="ex: politique retour">
                </div>
                
                <div class="form-group">
                    <label for="question">Votre question </label>
                    <textarea name="contenu" id="question" rows="3" required 
                              placeholder="Décrivez votre question en détail..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Publier la question</button>
            </form>
        </div>
    <?php else: ?>
        <p><a href="../connexion.php">Connectez-vous</a> pour poser une question.</p>
    <?php endif; ?>
    
    <!-- questions precedentes -->
    <div id="questionsList">
        <?php foreach ($questions as $question): ?>
            <div class="question-card" data-id="<?php echo $question['id_question']; ?>">
                <div class="question-header">
                    <div class="author-info">
                        <strong><?php echo ($question['prenom']); ?></strong>
                        <span>a demandé sur:</span>
                        <span style="color: #ffa500;"><?php echo ($question['produit_nom']); ?></span>
                    </div>
                    <small><?php echo date('d/m/Y H:i', strtotime($question['date_creation'])); ?></small>
                </div>
                
                <h4><?php echo ($question['titre']); ?></h4>
                <p><?php echo nl2br(($question['contenu'])); ?></p>
                
                <div class="question-actions">
                    <?php if (isset($_SESSION['client'])): ?>
                        <button class="btn btn-primary" 
                                onclick="toggleReplyForm(<?php echo $question['id_question']; ?>, null)">
                            Répondre
                        </button>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['client']) && $_SESSION['client']['id_client'] == $question['id_client']): ?>
                        <button class="btn btn-delete" 
                                onclick="deleteQuestion(<?php echo $question['id_question']; ?>)">
                            Supprimer
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- form reply principale -->
                <div class="reply-form" id="replyForm<?php echo $question['id_question']; ?>_null">
                    <textarea class="reply-textarea" rows="2" 
                              placeholder="Votre réponse..."></textarea>
                    <button class="btn btn-primary" 
                            onclick="submitReply(<?php echo $question['id_question']; ?>, null)">
                        Envoyer
                    </button>
                </div>
                
                <!-- reply reply -->
                <div class="replies" id="replies<?php echo $question['id_question']; ?>"></div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($questions)): ?>
            <p>Aucune question pour le moment. Soyez le premier à poser une question !</p>
        <?php endif; ?>
    </div>
</div>

<p style="text-align: center; margin-top: 30px;"><a href="../index.php">Retour</a></p>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="../styles/produit.js"></script>
</body>
</html>