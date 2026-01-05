<?php
session_start();
require 'csrf.php';
$csrf_token = generateCsrfToken();
?>

<!DOCTYPE html>
<html>
<head>
<title>SparkUp - Connexion</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="styles/style1.css" type="text/css" media="screen" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="styles/interactions.js"></script>

</head>
<body>
<h1 id="titre">SparkUp !</h1>
<h2>Se connecter</h2>


<div id="error-message" class="alert alert-error" style="display:none;"></div>

<form method="POST" action="connecter_ajax.php" autocomplete="off" id="loginForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

    <div class="form-group">
        <label for="email">Adresse e-mail:</label>
        <input type="email" id="email" name="mail" required>
        <div class="error-message" id="error-email">Veuillez entrer une adresse email valide</div>
    </div>
    
    <div class="form-group">
        <label for="mtp">Mot de passe:</label>
        <input type="password" id="mtp" name="mdp" required>
        <div class="error-message" id="error-mdp">Le mot de passe ne peut pas être vide</div>
    </div>
    
    <p>
        <input type="submit" value="Se connecter" id="submitBtn" class="enabled">
    </p>
</form>

<p>Pas encore de compte ? <a href="nouveau.php">Créer un compte</a></p>
<p><a href="index.php">Retour</a></p>

<script>
$(document).ready(function() {
    var validationState = {
        email: false,
        mdp: false
    };

    function checkAllFields() {
        var allValid = validationState.email && validationState.mdp;
        $('#submitBtn').prop('disabled', !allValid).toggleClass('enabled', allValid);
    }

    //  email
    function validateEmail() {
        var $email = $('#email');
        var email = $email.val().trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email.length === 0) {
            $email.removeClass('invalid valid');
            $('#error-email').hide();
            validationState.email = false;
        } else if (emailRegex.test(email)) {
            $email.removeClass('invalid').addClass('valid');
            $('#error-email').hide();
            validationState.email = true;
        } else {
            $email.removeClass('valid').addClass('invalid');
            $('#error-email').show();
            validationState.email = false;
        }
        checkAllFields();
    }

    // mdp
    function validatePassword() {
        var $mdp = $('#mtp');
        var password = $mdp.val();
        
        if (password.length === 0) {
            $mdp.removeClass('invalid valid');
            $('#error-mdp').hide();
            validationState.mdp = false;
        } else {
            $mdp.removeClass('invalid').addClass('valid');
            $('#error-mdp').hide();
            validationState.mdp = true;
        }
        checkAllFields();
    }

    // eventi
    $('#email').on('blur input', validateEmail);
    $('#mtp').on('blur input', validatePassword);

    // inizializzo validazione
    $('#submitBtn').prop('disabled', true).removeClass('enabled');

    // soumis AJAX
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!validationState.email || !validationState.mdp) {
            $('#error-message').html('Veuillez remplir tous les champs correctement.').fadeIn();
            return;
        }
        
        //bout disabled se i campi non validi
        $('#submitBtn').prop('disabled', true).text('Connexion en cours...');
        
        $.ajax({
            url: 'connecter_ajax.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#success-message').fadeIn();
                    $('#error-message').fadeOut();
                    setTimeout(() => window.location.href = 'index.php', 1000);
                } else {
                    $('#error-message').html(response.message).fadeIn();
                    $('#success-message').fadeOut();
                    $('#submitBtn').prop('disabled', false).text('Se connecter');
                }
            },
            error: function() {
                $('#error-message').html('Une erreur est survenue. Veuillez réessayer.').fadeIn();
                $('#submitBtn').prop('disabled', false).text('Se connecter');
            }
        });
    });
});
</script>

</body>
</html>