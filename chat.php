<?php

if (!isset($_SESSION['client'])) {
    return; // pas afficher le chat si non connecté
}

require_once 'csrf.php';
$csrf_token = generateCsrfToken();
?>

<link rel="stylesheet" href="styles/style_chat.css" type="text/css" media="screen" />

<div id="chat-widget">
    <div id="chat-header">
        <span>CHAT</span>
    </div>
    
    <!-- chiuso (classe minimized) -->
    <div id="chat-body" class="minimized">
        <div id="chat-messages">
            <!-- mess chargé con ajax -->
        </div>
        
        <div id="chat-input-container">
            <form id="chat-form">
                <input type="hidden" name="csrf_token" value="<?php echo ($csrf_token); ?>">
                <div id="chat-input-wrapper">
                    <input 
                        type="text" 
                        id="chat-message-input" 
                        name="message" 
                        placeholder="Tapez votre message..." 
                        maxlength="256"
                        autocomplete="off"
                    >
                    <span id="chat-char-count">0/256</span>
                </div>
                <button type="submit" id="chat-send-btn">Envoyer</button>

            </form>

            <div id="chat-error"></div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    let updateInterval;
    let isMinimized = true; 
    
    // toggle chat minimization
    $('#chat-header, #chat-toggle').on('click', function() {
        isMinimized = !isMinimized;
        $('#chat-body').toggleClass('minimized');        
        // qnd aperto carica messaggi
        if (!isMinimized) {
            loadMessages();
        }
    });
    
    // cmpt caracteres
    $('#chat-message-input').on('input', function() {
        const length = $(this).val().length;
        const $counter = $('#chat-char-count');
        $counter.text(length + '/256');
        
        $counter.removeClass('warning danger');
        
        if (length > 256) {
            $counter.addClass('danger');
        } else if (length > 200) {
            $counter.addClass('warning');
        }
    });
    
    // charger les messages
    function loadMessages() {
        $.ajax({
            url: 'chat_load.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayMessages(response.messages);
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur chargement messages:', error);
            }
        });
    }
    
    // afficher les messages
    function displayMessages(messages) {
        const $container = $('#chat-messages');
        const scrolledToBottom = $container[0].scrollHeight - $container.scrollTop() <= $container.outerHeight() + 50;
        
        $container.empty();
        
        if (messages.length === 0) {
            $container.html('<p>Aucun message récent</p>');
            return;
        }
        
        const currentUserId = <?php echo $_SESSION['client']['id_client']; ?>;
        
        messages.forEach(function(msg) {
            const isOwn = msg.id_client == currentUserId;
            const messageClass = isOwn ? 'chat-message chat-message-own' : 'chat-message';
            
            const messageHtml = `
                <div class="${messageClass}">
                    <div class="chat-message-author">${escapeHtml(msg.prenom)} dit :</div>
                    <div class="chat-message-content">'${escapeHtml(msg.contenu)}'</div>
                    <div class="chat-message-time">${msg.temps_ecoule}</div>
                </div>
            `;
            
            $container.append(messageHtml);
        });
        
        // scroll si on est deja en bas
        if (scrolledToBottom) {
            $container.scrollTop($container[0].scrollHeight);
        }
    }
    
    //  échapper le HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
    
    // send de message
    $('#chat-form').on('submit', function(e) {
        e.preventDefault();
        
        const $input = $('#chat-message-input');
        const message = $input.val().trim();
        
        if (message.length === 0) {
            showError('Le message ne peut pas être vide');
            return;
        }
        
        if (message.length > 256) {
            showError('Le message ne peut pas dépasser 256 caractères');
            return;
        }
        
        $('#chat-send-btn').prop('disabled', true);
        $('#chat-error').hide();
        
        $.ajax({
            url: 'chat_send.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $input.val('');
                    $('#chat-char-count').text('0/256').removeClass('warning danger');
                    loadMessages();
                } else {
                    showError(response.message || 'Erreur lors de l\'envoi du message');
                }
            },
            error: function(xhr, status, error) {
                showError('Erreur de connexion. Veuillez réssayer.');
            },
            complete: function() {
                $('#chat-send-btn').prop('disabled', false);
            }
        });
    });
    
    //  afficher les erreurs
    function showError(message) {
        $('#chat-error').text(message).fadeIn();
        setTimeout(() => $('#chat-error').fadeOut(), 5000);
    }
    
    // actualisation si ouvert
    updateInterval = setInterval(function() {
        if (!isMinimized) {
            loadMessages();
        }
    }, 3000);
    
    // quitte la page, pad d'actualisation
    $(window).on('beforeunload', function() {
        clearInterval(updateInterval);
    });
});
</script>