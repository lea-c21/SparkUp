// ============================================
// GESTIONE RATING STELLE (CORRETTO - da sinistra a destra)
// ============================================

$(document).ready(function() {
    let selectedRating = 0;
    
    // Click sulle stelle
    $('#starRating span').on('click', function() {
        selectedRating = $(this).data('value');
        $('#noteInput').val(selectedRating);
        highlightStars(selectedRating);
    });
    
    // Effetto hover
    $('#starRating span').on('mouseenter', function() {
        const rating = $(this).data('value');
        highlightStars(rating);
    });
    
    // Reset al mouseout
    $('#starRating').on('mouseleave', function() {
        highlightStars(selectedRating);
    });
    
    // Funzione per evidenziare le stelle DA SINISTRA A DESTRA
    function highlightStars(value) {
        $('#starRating span').each(function() {
            // CORRETTO: <= per riempire da sinistra a destra
            if ($(this).data('value') <= value) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });
    }
    
    // ============================================
    // SUBMIT COMMENTAIRE
    // ============================================
    
    $('#commentForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!selectedRating) {
            showError('comment', 'Veuillez sélectionner une note');
            return;
        }
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '../api/submit_comment.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess('comment', 'Votre avis a été publié avec succès!');
                    $('#commentForm')[0].reset();
                    selectedRating = 0;
                    highlightStars(0);
                    
                    // Recharge la page après 1 seconde
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showError('comment', response.message || 'Erreur lors de la publication');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', xhr.responseText);
                showError('comment', 'Erreur de connexion. Veuillez réessayer.');
            }
        });
    });
    
    // ============================================
    // SUBMIT QUESTION
    // ============================================
    
    $('#questionForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        form.find('input[name="note"]').remove();
        
        const formData = form.serialize();
        
        $.ajax({
            url: '../api/submit_question.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showSuccess('question', 'Votre question a été publiée avec succès!');
                    $('#questionForm')[0].reset();
                    
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showError('question', response.message || 'Erreur lors de la publication');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', xhr.responseText);
                showError('question', 'Erreur de connexion. Veuillez réessayer.');
            }
        });
    });
    
    // ============================================
    // RECHERCHE QUESTIONS
    // ============================================
    
    $('#searchQuestions').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('.question-card').each(function() {
            const titre = $(this).find('h4').text().toLowerCase();
            const contenu = $(this).find('p').text().toLowerCase();
            
            if (titre.includes(searchTerm) || contenu.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Carica risposte per ogni domanda
    $('.question-card').each(function() {
        const questionId = $(this).data('id');
        loadReplies(questionId);
    });
});

// ============================================
// LIKE COMMENTAIRE
// ============================================

function likeComment(commentId) {
    $.ajax({
        url: '../api/like_comment.php',
        method: 'POST',
        data: {
            id_commentaire: commentId,
            csrf_token: $('input[name="csrf_token"]').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Erreur');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur like:', xhr.responseText);
            alert('Erreur de connexion');
        }
    });
}

// ============================================
// DELETE COMMENTAIRE
// ============================================

function deleteComment(commentId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce commentaire?')) {
        return;
    }
    
    const csrfToken = $('input[name="csrf_token"]').first().val();
    
    if (!csrfToken) {
        alert('Erreur: Token CSRF manquant');
        return;
    }
    
    $.ajax({
        url: '../api/delete_comment.php',
        method: 'POST',
        data: {
            id_commentaire: commentId,
            csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Commentaire supprimé!');
                location.reload();
            } else {
                alert(response.message || 'Erreur');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur delete:', xhr.responseText);
            alert('Erreur de connexion: ' + error);
        }
    });
}

// ============================================
// DELETE QUESTION
// ============================================

function deleteQuestion(questionId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette question et toutes ses réponses?')) {
        return;
    }
    
    const csrfToken = $('input[name="csrf_token"]').first().val();
    
    if (!csrfToken) {
        alert('Erreur: Token CSRF manquant');
        return;
    }
    
    $.ajax({
        url: '../api/delete_question.php',
        method: 'POST',
        data: {
            id_question: questionId,
            csrf_token: csrfToken
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Question supprimée!');
                location.reload();
            } else {
                alert(response.message || 'Erreur');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur delete:', xhr.responseText);
            alert('Erreur de connexion: ' + error);
        }
    });
}

// ============================================
// TOGGLE REPLY FORM
// ============================================

function toggleReplyForm(questionId, parentId) {
    const formId = parentId !== null ? `replyForm${questionId}_${parentId}` : `replyForm${questionId}_null`;
    const form = $(`#${formId}`);
    
    $('.reply-form').not(`#${formId}`).slideUp();
    form.slideToggle();
}

// ============================================
// SUBMIT REPLY
// ============================================

function submitReply(questionId, parentId) {
    const formId = parentId !== null ? `replyForm${questionId}_${parentId}` : `replyForm${questionId}_null`;
    const textarea = $(`#${formId} .reply-textarea`);
    
    const contenu = textarea.val().trim();
    
    if (!contenu) {
        alert('Veuillez entrer une réponse');
        return;
    }
    
    $.ajax({
        url: '../api/submit_reply.php',
        method: 'POST',
        data: {
            id_question: questionId,
            id_parent: parentId,
            contenu: contenu,
            csrf_token: $('input[name="csrf_token"]').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                textarea.val('');
                $(`#${formId}`).slideUp();
                loadReplies(questionId);
            } else {
                alert(response.message || 'Erreur');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur reply:', xhr.responseText);
            alert('Erreur de connexion');
        }
    });
}

// ============================================
// LOAD REPLIES
// ============================================

function loadReplies(questionId) {
    $.ajax({
        url: '../api/get_replies.php',
        method: 'GET',
        data: { id_question: questionId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderReplies(questionId, response.replies);
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur load replies:', xhr.responseText);
        }
    });
}

function renderReplies(questionId, replies, parentId = null, level = 0) {
    const containerId = parentId !== null ? `replies${questionId}_${parentId}` : `replies${questionId}`;
    const container = $(`#${containerId}`);
    
    const currentLevelReplies = replies.filter(r => {
        if (parentId === null) {
            return r.id_parent === null || r.id_parent === 0 || r.id_parent === '';
        }
        return r.id_parent == parentId;
    });
    
    if (currentLevelReplies.length === 0 && level === 0) {
        container.html('<p style="color: rgba(255,255,255,0.5); padding: 15px; font-style: italic;">Aucune réponse pour le moment.</p>');
        return;
    }
    
    container.empty();
    
    currentLevelReplies.forEach(reply => {
        const replyHtml = `
            <div class="reply-card" style="margin-left: ${level * 30}px;">
                <div class="comment-header">
                    <div class="author-info">
                        <strong>${escapeHtml(reply.prenom)}</strong>
                        ${reply.is_owner ? '<span style="background:#ffa500;color:#000;padding:2px 8px;border-radius:12px;font-size:11px;font-weight:bold;">Vous</span>' : ''}
                    </div>
                    <small>${formatDate(reply.date_creation)}</small>
                </div>
                <p>${escapeHtml(reply.contenu).replace(/\n/g, '<br>')}</p>
                <div class="comment-actions">
                    ${reply.can_reply ? `
                        <button class="btn btn-primary btn-sm" 
                                onclick="toggleReplyForm(${questionId}, ${reply.id_reponse})">
                            Répondre
                        </button>
                    ` : ''}
                    ${reply.is_owner ? `
                        <button class="btn btn-delete btn-sm" 
                                onclick="deleteReply(${reply.id_reponse}, ${questionId})">
                            Supprimer
                        </button>
                    ` : ''}
                </div>
                
                <div class="reply-form" id="replyForm${questionId}_${reply.id_reponse}">
                    <textarea class="reply-textarea" rows="2" placeholder="Votre réponse..."></textarea>
                    <button class="btn btn-primary btn-sm" 
                            onclick="submitReply(${questionId}, ${reply.id_reponse})">
                        Envoyer
                    </button>
                </div>
                
                <div class="replies" id="replies${questionId}_${reply.id_reponse}"></div>
            </div>
        `;
        
        container.append(replyHtml);
        renderReplies(questionId, replies, reply.id_reponse, level + 1);
    });
}

// ============================================
// DELETE REPLY
// ============================================

function deleteReply(replyId, questionId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette réponse?')) {
        return;
    }
    
    $.ajax({
        url: '../api/delete_reply.php',
        method: 'POST',
        data: {
            id_reponse: replyId,
            csrf_token: $('input[name="csrf_token"]').val()
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                loadReplies(questionId);
            } else {
                alert(response.message || 'Erreur');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erreur delete:', xhr.responseText);
            alert('Erreur de connexion');
        }
    });
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function showError(section, message) {
    $(`#${section}-error`).text(message).fadeIn();
    setTimeout(() => $(`#${section}-error`).fadeOut(), 5000);
}

function showSuccess(section, message) {
    $(`#${section}-success`).text(message).fadeIn();
    setTimeout(() => $(`#${section}-success`).fadeOut(), 5000);
}

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

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}