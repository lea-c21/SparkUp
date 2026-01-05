

(function() {
    'use strict';
    
    // Attendre que le DOM sia completamente caricato
    document.addEventListener('DOMContentLoaded', function() {
        
        // ===== EFFETTI HOVER SUI LINK =====
        const links = document.querySelectorAll('a:not(table a)');
        links.forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 10px 25px rgba(251, 191, 36, 0.3)';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
        
        // ===== EFFETTI HOVER SUI BOTTONI =====
        const buttons = document.querySelectorAll('input[type="submit"].enabled, .btn-commander, .btn-valider');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 15px 35px rgba(236, 72, 153, 0.4)';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
        
        // ===== EFFETTI HOVER SULLE RIGHE DELLA TABELLA =====
        const tableRows = document.querySelectorAll('table tr:not(:first-child)');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                const cells = this.querySelectorAll('td');
                cells.forEach(cell => {
                    cell.style.background = '#293548';
                });
            });
            
            row.addEventListener('mouseleave', function() {
                const cells = this.querySelectorAll('td');
                cells.forEach(cell => {
                    cell.style.background = '';
                });
            });
        });
        
        // ===== EFFETTI HOVER SUI LINK DELLA TABELLA =====
        const tableLinks = document.querySelectorAll('table td a');
        tableLinks.forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.color = '#fcd34d';
                this.style.textDecoration = 'underline';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.color = '#fbbf24';
                this.style.textDecoration = 'none';
            });
        });
        
        // ===== EFFETTI FOCUS SUGLI INPUT =====
        const inputs = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], input[type="number"]');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.style.borderColor = '#fbbf24';
                this.style.background = '#1e293b';
                this.style.boxShadow = '0 0 0 3px rgba(251, 191, 36, 0.2)';
            });
            
            input.addEventListener('blur', function() {
                if (!this.classList.contains('valid') && !this.classList.contains('invalid')) {
                    this.style.borderColor = '#334155';
                    this.style.background = '#0f172a';
                    this.style.boxShadow = 'none';
                }
            });
        });
        
        // ===== ANIMAZIONE FADE-IN PER MESSAGGI DI ERRORE =====
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(msg => {
            // Osserva quando il messaggio diventa visibile
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.target.style.display === 'block' || 
                        mutation.target.classList.contains('show')) {
                        fadeIn(mutation.target);
                    }
                });
            });
            
            observer.observe(msg, {
                attributes: true,
                attributeFilter: ['style', 'class']
            });
        });
        
        // ===== ANIMAZIONE FADE-IN PER ALERT =====
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert.style.display !== 'none') {
                fadeIn(alert);
            }
        });
        
        // ===== CONFERMA ELIMINAZIONE ARTICOLO DAL PANIER =====
        const deleteLinks = document.querySelectorAll('a[href*="supprimer"]');
        deleteLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Voulez-vous vraiment supprimer cet article du panier ?')) {
                    e.preventDefault();
                }
            });
        });
        
        // ===== CONFERMA SVUOTARE IL PANIER =====
        const emptyCartLinks = document.querySelectorAll('a[href*="vider_panier"]');
        emptyCartLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Voulez-vous vraiment vider tout le panier ?')) {
                    e.preventDefault();
                }
            });
        });
        
        // ===== VALIDAZIONE NUMERO INPUT (MIN/MAX) =====
        const numberInputs = document.querySelectorAll('input[type="number"]');
        numberInputs.forEach(input => {
            input.addEventListener('change', function() {
                const min = parseInt(this.getAttribute('min')) || 0;
                const max = parseInt(this.getAttribute('max')) || Infinity;
                let value = parseInt(this.value);
                
                if (value < min) {
                    this.value = min;
                    showTemporaryMessage(this, 'Valeur minimale: ' + min, 'error');
                } else if (value > max) {
                    this.value = max;
                    showTemporaryMessage(this, 'Valeur maximale: ' + max, 'error');
                }
            });
        });
        
        // ===== SMOOTH SCROLL PER LINK INTERNI =====
        const internalLinks = document.querySelectorAll('a[href^="#"]');
        internalLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // ===== ANIMAZIONE CLICK SUI BOTTONI =====
        const allButtons = document.querySelectorAll('button, input[type="submit"], .btn-commander, .btn-valider');
        allButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Effetto ripple
                const ripple = document.createElement('span');
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.6)';
                ripple.style.width = '20px';
                ripple.style.height = '20px';
                ripple.style.animation = 'ripple 0.6s ease-out';
                ripple.style.pointerEvents = 'none';
                
                const rect = this.getBoundingClientRect();
                ripple.style.left = (e.clientX - rect.left - 10) + 'px';
                ripple.style.top = (e.clientY - rect.top - 10) + 'px';
                
                if (this.style.position !== 'absolute' && this.style.position !== 'relative') {
                    this.style.position = 'relative';
                }
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
        
        // ===== HIGHLIGHT TEMPORANEO PER INPUT DOPO SUBMIT FALLITO =====
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const invalidInputs = this.querySelectorAll('input.invalid');
                if (invalidInputs.length > 0) {
                    invalidInputs.forEach(input => {
                        pulseAnimation(input);
                    });
                }
            });
        });
        
    });
    
    // ===== FUNZIONI HELPER =====
    
    function fadeIn(element, duration = 300) {
        element.style.opacity = '0';
        element.style.display = 'block';
        
        let opacity = 0;
        const increment = 50 / duration;
        
        const timer = setInterval(function() {
            opacity += increment;
            element.style.opacity = opacity.toString();
            
            if (opacity >= 1) {
                clearInterval(timer);
                element.style.opacity = '1';
            }
        }, 50);
    }
    
    function showTemporaryMessage(element, message, type = 'info') {
        const messageDiv = document.createElement('div');
        messageDiv.textContent = message;
        messageDiv.style.cssText = `
            position: absolute;
            background: ${type === 'error' ? '#7f1d1d' : '#064e3b'};
            color: ${type === 'error' ? '#fca5a5' : '#a7f3d0'};
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            z-index: 1000;
            margin-top: 5px;
        `;
        
        element.parentNode.style.position = 'relative';
        element.parentNode.appendChild(messageDiv);
        
        setTimeout(() => {
            messageDiv.remove();
        }, 3000);
    }
    
    function pulseAnimation(element) {
        element.style.animation = 'pulse 0.5s ease-in-out';
        setTimeout(() => {
            element.style.animation = '';
        }, 500);
    }
    
    // ===== AGGIUNGI ANIMAZIONI CSS DINAMICAMENTE =====
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            from {
                width: 20px;
                height: 20px;
                opacity: 1;
            }
            to {
                width: 300px;
                height: 300px;
                opacity: 0;
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-10px);
            }
            75% {
                transform: translateX(10px);
            }
        }
    `;
    document.head.appendChild(style);
    
})();