/**
 * ============================================================================
 * COZY GAMING — Script Codes Ami (v2.0 — Plateformes)
 * ============================================================================
 *
 * Gère la sauvegarde AJAX et la validation en temps réel
 * des codes ami par plateforme dans le formulaire front-end.
 */

(function () {
    'use strict';

    var form = document.getElementById('cozy-friend-codes-form');
    if (!form) return;

    var messageEl = document.getElementById('cozy-fc-message');
    var submitBtn = form.querySelector('.cozy-friend-codes__btn');

    // -----------------------------------------------
    // Initialiser les icônes Lucide injectées dans le form
    // -----------------------------------------------
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // -----------------------------------------------
    // Validation en temps réel à la saisie
    // -----------------------------------------------
    var inputs = form.querySelectorAll('.cozy-friend-codes__input-wrapper input[data-pattern]');

    inputs.forEach(function (input) {
        input.addEventListener('blur', function () {
            validateInput(input);
        });

        input.addEventListener('input', function () {
            input.classList.remove('cozy-fc-invalid', 'cozy-fc-valid');
        });
    });

    function validateInput(input) {
        var value = input.value.trim();
        var pattern = input.getAttribute('data-pattern');

        if (!value) {
            input.classList.remove('cozy-fc-invalid', 'cozy-fc-valid');
            return true;
        }

        if (!pattern) return true;

        try {
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
            return true;
        }
    }

    // -----------------------------------------------
    // Formatage automatique des codes Nintendo Switch
    // -----------------------------------------------
    form.querySelectorAll('.cozy-friend-codes__field[data-platform="nintendo-switch"] input').forEach(function (input) {
        input.addEventListener('input', function () {
            var val = input.value.toUpperCase().replace(/[^SW0-9\-]/g, '');

            if (val.length >= 2 && val.substring(0, 2) === 'SW' && val[2] !== '-') {
                val = 'SW-' + val.substring(2);
            }

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
    });

    // -----------------------------------------------
    // Formatage Riot ID : ajouter # si oublié
    // -----------------------------------------------
    form.querySelectorAll('.cozy-friend-codes__field[data-platform="riot-games"] input').forEach(function (input) {
        input.addEventListener('blur', function () {
            var val = input.value.trim();
            if (val && val.indexOf('#') === -1) {
                // Ne pas auto-corriger, juste marquer invalide
                input.classList.add('cozy-fc-invalid');
            }
        });
    });

    // -----------------------------------------------
    // Formatage Battle.net : ajouter # si oublié
    // -----------------------------------------------
    form.querySelectorAll('.cozy-friend-codes__field[data-platform="battle-net"] input').forEach(function (input) {
        input.addEventListener('blur', function () {
            var val = input.value.trim();
            if (val && val.indexOf('#') === -1) {
                input.classList.add('cozy-fc-invalid');
            }
        });
    });

    // -----------------------------------------------
    // Soumission AJAX du formulaire
    // -----------------------------------------------
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var hasErrors = false;
        inputs.forEach(function (input) {
            if (!validateInput(input)) {
                hasErrors = true;
            }
        });

        if (hasErrors) {
            messageEl.innerHTML = 'Certains identifiants ont un format invalide. Vérifie les champs en rouge.';
            messageEl.className = 'cozy-friend-codes__message cozy-friend-codes__message--error';
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader"></i> Sauvegarde…';
        if (typeof lucide !== 'undefined') lucide.createIcons();
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
            submitBtn.innerHTML = '<i data-lucide="save"></i> Sauvegarder mes codes';
            if (typeof lucide !== 'undefined') lucide.createIcons();

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
            var platformSlug = field.getAttribute('data-platform');
            var wrapper = field.querySelector('.cozy-friend-codes__input-wrapper');
            var status = wrapper.querySelector('.cozy-friend-codes__status');

            var hasSavedCode = savedCodes && savedCodes[platformSlug];

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
