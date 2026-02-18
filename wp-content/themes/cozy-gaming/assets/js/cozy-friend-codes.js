/**
 * ============================================================================
 * COZY GAMING — Script Codes Ami
 * ============================================================================
 *
 * Gère la sauvegarde AJAX et la validation en temps réel
 * des codes ami dans le formulaire front-end.
 */

(function () {
    'use strict';

    var form = document.getElementById('cozy-friend-codes-form');
    if (!form) return;

    var messageEl = document.getElementById('cozy-fc-message');
    var submitBtn = form.querySelector('.cozy-friend-codes__btn');

    // -----------------------------------------------
    // Validation en temps réel à la saisie
    // -----------------------------------------------
    var inputs = form.querySelectorAll('.cozy-friend-codes__input-wrapper input[data-pattern]');
    
    inputs.forEach(function (input) {
        input.addEventListener('blur', function () {
            validateInput(input);
        });

        input.addEventListener('input', function () {
            // Retirer l'état d'erreur pendant la saisie
            input.classList.remove('cozy-fc-invalid', 'cozy-fc-valid');
        });
    });

    function validateInput(input) {
        var value = input.value.trim();
        var pattern = input.getAttribute('data-pattern');

        // Champ vide = OK (non obligatoire)
        if (!value) {
            input.classList.remove('cozy-fc-invalid', 'cozy-fc-valid');
            return true;
        }

        if (!pattern) return true;

        try {
            // Convertir le pattern PHP en regex JS
            // Retirer les délimiteurs PHP (premier et dernier caractère)
            var jsPattern = pattern.slice(1, pattern.lastIndexOf(pattern[0]));
            var regex = new RegExp(jsPattern);

            if (regex.test(value)) {
                input.classList.remove('cozy-fc-invalid');
                input.classList.add('cozy-fc-valid');
                return true;
            } else {
                input.classList.remove('cozy-fc-valid');
                input.classList.add('cozy-fc-invalid');
                return false;
            }
        } catch (e) {
            return true; // En cas d'erreur de regex, on laisse passer
        }
    }

    // -----------------------------------------------
    // Formatage automatique des codes Switch
    // -----------------------------------------------
    inputs.forEach(function (input) {
        if (input.placeholder && input.placeholder.indexOf('SW-') === 0) {
            input.addEventListener('input', function () {
                var val = input.value.toUpperCase().replace(/[^SW0-9\-]/g, '');
                
                // Auto-formatage : ajouter SW- et les tirets
                if (val.length >= 2 && val.substring(0, 2) === 'SW' && val[2] !== '-') {
                    val = 'SW-' + val.substring(2);
                }

                // Ajouter les tirets après les groupes de 4 chiffres
                var parts = val.replace('SW-', '').replace(/-/g, '');
                if (parts.length > 0) {
                    var formatted = 'SW-';
                    for (var i = 0; i < parts.length && i < 12; i++) {
                        if (i > 0 && i % 4 === 0) {
                            formatted += '-';
                        }
                        formatted += parts[i];
                    }
                    input.value = formatted;
                }
            });
        }
    });

    // -----------------------------------------------
    // Soumission AJAX du formulaire
    // -----------------------------------------------
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Valider tous les champs avant envoi
        var hasErrors = false;
        inputs.forEach(function (input) {
            if (!validateInput(input)) {
                hasErrors = true;
            }
        });

        if (hasErrors) {
            messageEl.innerHTML = 'Certains codes ont un format invalide. Vérifie les champs en rouge.';
            messageEl.className = 'cozy-friend-codes__message cozy-friend-codes__message--error';
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Sauvegarde…';
        messageEl.textContent = '';
        messageEl.className = 'cozy-friend-codes__message';

        var formData = new FormData(form);
        formData.append('action', 'cozy_save_friend_codes');
        formData.append('nonce', cozyFriendCodes.nonce);

        fetch(cozyFriendCodes.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.success) {
                messageEl.textContent = data.data.message;
                messageEl.className = 'cozy-friend-codes__message cozy-friend-codes__message--success';

                // Mettre à jour les indicateurs de statut
                updateStatuses(data.data.codes);
            } else {
                messageEl.innerHTML = data.data.message;
                messageEl.className = 'cozy-friend-codes__message cozy-friend-codes__message--error';
            }
        })
        .catch(function () {
            messageEl.textContent = 'Une erreur est survenue. Vérifie ta connexion et réessaie.';
            messageEl.className = 'cozy-friend-codes__message cozy-friend-codes__message--error';
        })
        .finally(function () {
            submitBtn.disabled = false;
            submitBtn.textContent = '💾 Sauvegarder mes codes';

            setTimeout(function () {
                messageEl.style.opacity = '0';
                setTimeout(function () {
                    messageEl.textContent = '';
                    messageEl.style.opacity = '1';
                }, 300);
            }, 5000);
        });
    });

    /**
     * Met à jour les indicateurs de statut (✓) après sauvegarde
     */
    function updateStatuses(savedCodes) {
        var fields = form.querySelectorAll('.cozy-friend-codes__field');
        
        fields.forEach(function (field) {
            var gameSlug = field.getAttribute('data-game');
            var wrapper = field.querySelector('.cozy-friend-codes__input-wrapper');
            var status = wrapper.querySelector('.cozy-friend-codes__status');

            var hasSavedCode = savedCodes && savedCodes[gameSlug];

            if (hasSavedCode) {
                if (!status) {
                    status = document.createElement('span');
                    status.className = 'cozy-friend-codes__status cozy-friend-codes__status--saved';
                    wrapper.appendChild(status);
                }
                status.textContent = '✓';
                status.className = 'cozy-friend-codes__status cozy-friend-codes__status--saved';
            } else if (status) {
                status.remove();
            }
        });
    }

})();
