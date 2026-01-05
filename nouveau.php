<?php
session_start();
require 'csrf.php';

$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html>
<head>
<title>SparkUp - Nouveau Client</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" href="styles/style_nouveau.css" type="text/css" media="screen" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body>
<h1 id="titre">SparkUp !</h1>
<h2>Nouveau Client</h2>

<form method="post" action="enregistrement_ajax.php" autocomplete="off" id="registrationForm">
    <input type="hidden" name="csrf_token" value="<?php echo ($csrf_token); ?>">
    
    <div class="form-group">
        <label for="nom">Nom:</label>
        <input type="text" id="nom" name="n" value="">
        <div class="error-message" id="error-nom">Le nom ne peut pas être vide</div>
    </div>

    <div class="form-group">
        <label for="prenom">Prénom:</label>
        <input type="text" id="prenom" name="p" value="">
        <div class="error-message" id="error-prenom">Le prénom ne peut pas être vide</div>
    </div>

    <div class="form-group">
        <label for="adresse">Adresse:</label>
        <input type="text" id="adresse" name="adr" value="">
        <div class="error-message" id="error-adresse">L'adresse ne peut pas être vide</div>
    </div>

    <div class="form-group">
        <label for="numero">Numéro de téléphone:</label>
        <input type="text" id="numero" name="num" value="">
        <div class="error-message" id="error-numero">Format numéro invalide</div>
    </div>

    <div class="form-group">
        <label for="mail">Adresse e-mail:</label>
        <input type="email" id="mail" name="mail" value="">
        <div class="error-message" id="error-mail">Format d'email invalide</div>
        <div class="error-message" id="error-mail-existe">Cet email est déjà utilisé</div>
    </div>

    <div class="form-group">
        <label for="mdp1">Mot de passe:</label>
        <input type="password" id="mdp1" name="mdp1" value="">
        <div class="error-message" id="error-mdp1">Le mot de passe doit contenir au moins 8 caractères, 1 lettre, 1 chiffre et 1 caractère spécial</div>
    </div>

    <div class="form-group">
        <label for="mdp2">Confirmer votre mot de passe:</label>
        <input type="password" id="mdp2" name="mdp2" value="">
        <div class="error-message" id="error-mdp2">Les mots de passe ne correspondent pas</div>
    </div>

    <p>
        <input type="submit" value="Créer mon compte" id="submitBtn" disabled>
    </p>
    
    <div id="error-message" class="alert alert-error" style="display:none;"></div>
</form>

<p><a id="retour" href="index.php">Retour</a></p>

<script>
$(document).ready(function() {
    var validationState = {
        nom: false,
        prenom: false,
        adresse: false,
        numero: false,
        mail: false,
        mdp1: false,
        mdp2: false
    };

    function checkAllFields() {
        var allValid = Object.values(validationState).every(v => v === true);
        $('#submitBtn').prop('disabled', !allValid);
    }

    function showError($errorElement) {
        $errorElement.addClass('show');
    }

    function hideError($errorElement) {
        $errorElement.removeClass('show');
    }

    // campi vuoti no erreur
    function validateNotEmpty(fieldId) {
        var $field = $('#' + fieldId);
        var value = $field.val().trim();
        
        if (value.length === 0) {
            $field.removeClass('invalid valid');
            validationState[fieldId] = false;
        } else {
            $field.removeClass('invalid').addClass('valid');
            validationState[fieldId] = true;
        }
        checkAllFields();
    }

    // validation numero tel
    function validateNumero() {
        var $numero = $('#numero');
        var numero = $numero.val().trim();
        var $error = $('#error-numero');
        var numeroRegex = /^[0-9\s\+\-\(\)\.]{8,20}$/;
        
        if (numero.length === 0) {
            $numero.removeClass('invalid valid');
            hideError($error);
            validationState.numero = false;
        } else if (!numeroRegex.test(numero)) {
            $numero.removeClass('valid').addClass('invalid');
            showError($error);
            validationState.numero = false;
        } else {
            $numero.removeClass('invalid').addClass('valid');
            hideError($error);
            validationState.numero = true;
        }
        checkAllFields();
    }

    // validation format email
    var lastCheckedEmail = '';
    
    function validateEmail(forceCheck) {
        var $mail = $('#mail');
        var email = $mail.val().trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        var $errorFormat = $('#error-mail');
        var $errorExists = $('#error-mail-existe');

        if (email.length === 0) {
            $mail.removeClass('invalid valid');
            hideError($errorFormat);
            hideError($errorExists);
            validationState.mail = false;
            checkAllFields();
            return;
        }
        
        if (!emailRegex.test(email)) {
            $mail.removeClass('valid').addClass('invalid');
            showError($errorFormat);
            hideError($errorExists);
            validationState.mail = false;
            checkAllFields();
            return;
        }
        
        hideError($errorFormat);
        
        // controllo esistenza e se mail changé
        if (forceCheck && email !== lastCheckedEmail) {
            lastCheckedEmail = email;
            $.ajax({
                url: 'verifier_email.php',
                method: 'POST',
                data: { 
                    email: email,
                    csrf_token: $('input[name="csrf_token"]').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.existe) {
                        $mail.removeClass('valid').addClass('invalid');
                        showError($errorExists);
                        validationState.mail = false;
                    } else {
                        $mail.removeClass('invalid').addClass('valid');
                        hideError($errorExists);
                        validationState.mail = true;
                    }
                    checkAllFields();
                },
                error: function() {
                    $mail.removeClass('invalid').addClass('valid');
                    hideError($errorExists);
                    validationState.mail = true;
                    checkAllFields();
                }
            });
        } else if (!forceCheck) {
            // pendant digit seulm format
            $mail.removeClass('invalid').addClass('valid');
            hideError($errorExists);
            validationState.mail = true;
            checkAllFields();
        }
    }

    // validation psw
    function validatePassword() {
        var $mdp = $('#mdp1');
        var password = $mdp.val();
        var $error = $('#error-mdp1');
        
        if (password.length === 0) {
            $mdp.removeClass('invalid valid');
            hideError($error);
            validationState.mdp1 = false;
            checkAllFields();
            return;
        }
        
        var hasLetter = /[a-zA-Z]/.test(password);
        var hasNumber = /[0-9]/.test(password);
        var hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
        var isValid = password.length >= 8 && hasLetter && hasNumber && hasSpecial;
        
        if (isValid) {
            $mdp.removeClass('invalid').addClass('valid');
            hideError($error);
            validationState.mdp1 = true;
        } else {
            $mdp.removeClass('valid').addClass('invalid');
            showError($error);
            validationState.mdp1 = false;
        }
        
        if ($('#mdp2').val().length > 0) {
            validatePasswordConfirmation();
        }
        
        checkAllFields();
    }

    // validation psw2
    function validatePasswordConfirmation() {
        var $mdp2 = $('#mdp2');
        var password = $('#mdp1').val();
        var confirmation = $mdp2.val();
        var $error = $('#error-mdp2');
        
        if (confirmation.length === 0) {
            $mdp2.removeClass('invalid valid');
            hideError($error);
            validationState.mdp2 = false;
        } else if (password === confirmation && validationState.mdp1) {
            $mdp2.removeClass('invalid').addClass('valid');
            hideError($error);
            validationState.mdp2 = true;
        } else {
            $mdp2.removeClass('valid').addClass('invalid');
            showError($error);
            validationState.mdp2 = false;
        }
        checkAllFields();
    }

    // events
    $('#nom').on('input', function() { validateNotEmpty('nom'); });
    $('#prenom').on('input', function() { validateNotEmpty('prenom'); });
    $('#adresse').on('input', function() { validateNotEmpty('adresse'); });
    $('#numero').on('input', validateNumero);
    $('#mail').on('input', function() { validateEmail(false); });
    $('#mail').on('blur', function() { validateEmail(true); });
    $('#mdp1').on('input', validatePassword);
    $('#mdp2').on('input', validatePasswordConfirmation);

    // submit
    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!Object.values(validationState).every(v => v === true)) {
            $('#error-message').removeClass('alert-success').addClass('alert-error')
                .html('Veuillez corriger tous les champs avant de soumettre.').show();
            return;
        }
        
        $('#submitBtn').prop('disabled', true).text('Création en cours...');
        $('#error-message').hide();
        
        $.ajax({
            url: 'enregistrement_ajax.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    window.location.href = data.redirect || 'index.php';
                } else {
                    $('#error-message').removeClass('alert-success').addClass('alert-error')
                        .html(' ' + data.message).show();
                    $('#submitBtn').prop('disabled', false).text('Créer mon compte');
                }
            },
            error: function(xhr) {
                console.error('Errore AJAX:', xhr.responseText);
                $('#error-message').removeClass('alert-success').addClass('alert-error')
                    .html(' Une erreur est survenue. Veuillez réessayer.').show();
                $('#submitBtn').prop('disabled', false).text('Créer mon compte');
            }
        });
    });
});
</script>
</body>
</html>