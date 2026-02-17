/**
 * ============================================================================
 * COZY GAMING — Script Profils Sociaux (Discord & Twitch)
 * ============================================================================
 * 
 * Gère la sauvegarde AJAX du formulaire de profil social
 * et les retours visuels pour l'utilisateur.
 */

(function () {
    'use strict';

    var form = document.getElementById('cozy-social-form');
    if (!form) return;

    var messageEl  = document.getElementById('cozy-social-message');
    var submitBtn  = form.querySelector('.cozy-social-profile__btn');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Désactiver le bouton pendant le traitement
        submitBtn.disabled = true;
        submitBtn.textContent = '⏳ Sauvegarde…';
        messageEl.textContent = '';
        messageEl.className = 'cozy-social-profile__message';

        // Préparer les données
        var formData = new FormData(form);
        formData.append('action', 'cozy_save_social');
        formData.append('nonce', cozySocial.nonce);

        // Envoi AJAX
        fetch(cozySocial.ajaxUrl, {
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
                messageEl.className = 'cozy-social-profile__message cozy-social-profile__message--success';

                // Mettre à jour les statuts visuels
                updateStatus('cozy_discord_front', data.data.discord);
                updateStatus('cozy_twitch_front', data.data.twitch);
            } else {
                messageEl.innerHTML = data.data.message;
                messageEl.className = 'cozy-social-profile__message cozy-social-profile__message--error';
            }
        })
        .catch(function () {
            messageEl.textContent = 'Une erreur est survenue. Vérifie ta connexion et réessaie.';
            messageEl.className = 'cozy-social-profile__message cozy-social-profile__message--error';
        })
        .finally(function () {
            submitBtn.disabled = false;
            submitBtn.textContent = '💾 Sauvegarder mon profil';

            // Faire disparaître le message après 5 secondes
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
     * Met à jour le badge de statut (Lié / Non lié) à côté d'un champ
     */
    function updateStatus(inputId, value) {
        var input = document.getElementById(inputId);
        if (!input) return;

        var wrapper = input.closest('.cozy-social-profile__input-wrapper');
        if (!wrapper) return;

        var status = wrapper.querySelector('.cozy-social-profile__status');
        if (!status) {
            status = document.createElement('span');
            status.className = 'cozy-social-profile__status';
            wrapper.appendChild(status);
        }

        if (value && value.length > 0) {
            status.textContent = '✓ Lié';
            status.className = 'cozy-social-profile__status cozy-social-profile__status--linked';
        } else {
            status.textContent = 'Non lié';
            status.className = 'cozy-social-profile__status cozy-social-profile__status--unlinked';
        }
    }

})();
