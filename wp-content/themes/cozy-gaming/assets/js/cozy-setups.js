/**
 * ============================================================================
 * COZY SETUPS — Upload AJAX, Drag & Drop, Lightbox, Delete
 * ============================================================================
 */

(function () {
    'use strict';

    /* ------------------------------------------------------------------
       RÉFÉRENCES DOM
       ------------------------------------------------------------------ */
    const toggleBtn  = document.getElementById('cozy-setup-toggle-form');
    const form       = document.getElementById('cozy-setup-form');
    const cancelBtn  = document.getElementById('cozy-setup-cancel');
    const submitBtn  = document.getElementById('cozy-setup-submit');
    const fileInput  = document.getElementById('cozy-setup-photo');
    const dropzone   = document.getElementById('cozy-setup-dropzone');
    const previewZone = document.getElementById('cozy-setup-preview');
    const messageEl  = document.getElementById('cozy-setup-message');
    const lightbox   = document.getElementById('cozy-lightbox');

    /* ------------------------------------------------------------------
       1. TOGGLE FORMULAIRE
       ------------------------------------------------------------------ */
    if (toggleBtn && form) {
        toggleBtn.addEventListener('click', function () {
            const isVisible = form.style.display !== 'none';
            form.style.display = isVisible ? 'none' : 'block';
            toggleBtn.innerHTML = isVisible
                ? '<i data-lucide="camera"></i> Partager mon setup'
                : '<i data-lucide="x"></i> Fermer';
            refreshIcons();
        });
    }

    if (cancelBtn && form && toggleBtn) {
        cancelBtn.addEventListener('click', function () {
            form.style.display = 'none';
            toggleBtn.innerHTML = '<i data-lucide="camera"></i> Partager mon setup';
            refreshIcons();
            resetForm();
        });
    }

    /* ------------------------------------------------------------------
       2. DRAG & DROP + CLICK-TO-UPLOAD + PREVIEW
       ------------------------------------------------------------------ */

    // Clic sur la dropzone → ouvre le sélecteur de fichier
    if (dropzone && fileInput) {
        dropzone.addEventListener('click', function () {
            fileInput.click();
        });
    }

    // Clic sur la preview → permet de changer la photo
    if (previewZone && fileInput) {
        previewZone.addEventListener('click', function () {
            fileInput.click();
        });
    }

    if (dropzone) {
        ['dragenter', 'dragover'].forEach(function (evt) {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
        });

        ['dragleave', 'drop'].forEach(function (evt) {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.remove('dragover');
            });
        });

        dropzone.addEventListener('drop', function (e) {
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                showPreview(e.dataTransfer.files[0]);
            }
        });
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            if (fileInput.files.length > 0) {
                showPreview(fileInput.files[0]);
            }
        });
    }

    /**
     * Affiche l'aperçu de l'image sélectionnée
     */
    function showPreview(file) {
        if (!previewZone || !dropzone) return;

        var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (allowedTypes.indexOf(file.type) === -1) {
            showMessage('Format non supporté. Utilise JPG, PNG ou WebP.', 'error');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            showMessage('La photo ne doit pas dépasser 5 Mo.', 'error');
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            previewZone.src = e.target.result;
            previewZone.style.display = 'block';
            previewZone.style.cursor = 'pointer';
            dropzone.style.display = 'none';
        };
        reader.readAsDataURL(file);
        clearUploadError();
        showMessage('', '');
    }

    /* ------------------------------------------------------------------
       3. SOUMISSION AJAX
       ------------------------------------------------------------------ */
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!window.cozySetups) return;

            // Effacer les erreurs précédentes
            clearAllErrors();

            var title = document.getElementById('cozy-setup-title');
            var hasErrors = false;

            if (!title || !title.value.trim()) {
                setFieldError(title, 'Le titre est obligatoire.');
                hasErrors = true;
            }

            if (!fileInput || !fileInput.files.length) {
                setUploadError('Choisis une photo de ton setup.');
                hasErrors = true;
            }

            if (hasErrors) {
                showMessage('Corrige les champs en rouge pour continuer.', 'error');
                return;
            }

            // Préparer les données
            var formData = new FormData();
            formData.append('action', 'cozy_upload_setup');
            formData.append('nonce', cozySetups.nonce);
            formData.append('setup_title', title.value.trim());

            var desc = document.getElementById('cozy-setup-description');
            formData.append('setup_description', desc ? desc.value.trim() : '');
            formData.append('setup_photo', fileInput.files[0]);

            // Désactiver le bouton
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-lucide="loader"></i> Envoi en cours…';
            refreshIcons();
            showMessage('', '');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', cozySetups.ajaxUrl, true);

            xhr.onload = function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-lucide="upload"></i> Publier mon setup';
                refreshIcons();

                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showMessage(response.data.message, 'success');
                        resetForm();
                        // Masquer le formulaire après 2s
                        setTimeout(function () {
                            if (form) form.style.display = 'none';
                            if (toggleBtn) {
                                toggleBtn.innerHTML = '<i data-lucide="camera"></i> Partager mon setup';
                                refreshIcons();
                            }
                        }, 2500);
                    } else {
                        showMessage(response.data.message || 'Erreur inconnue.', 'error');
                    }
                } catch (err) {
                    showMessage('Erreur de communication avec le serveur.', 'error');
                }
            };

            xhr.onerror = function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-lucide="upload"></i> Publier mon setup';
                refreshIcons();
                showMessage('Erreur réseau. Vérifie ta connexion.', 'error');
            };

            xhr.send(formData);
        });
    }

    /* ------------------------------------------------------------------
       4. LIGHTBOX
       ------------------------------------------------------------------ */
    // Ouvrir la lightbox au clic sur une image de setup
    document.addEventListener('click', function (e) {
        var imageWrapper = e.target.closest('.cozy-setups__card-image');
        if (!imageWrapper) return;

        e.preventDefault();

        var card = imageWrapper.closest('.cozy-setups__card');
        if (!card) return;

        var fullSrc = imageWrapper.getAttribute('data-full');
        var titleEl = card.querySelector('.cozy-setups__card-title');
        var descEl  = card.querySelector('.cozy-setups__card-desc');
        var authorEl = card.querySelector('.cozy-setups__card-author span');

        var lbImg    = document.getElementById('cozy-lightbox-img');
        var lbTitle  = document.getElementById('cozy-lightbox-title');
        var lbDesc   = document.getElementById('cozy-lightbox-desc');
        var lbAuthor = document.getElementById('cozy-lightbox-author');

        if (lbImg)    lbImg.src = fullSrc || '';
        if (lbTitle)  lbTitle.textContent = titleEl ? titleEl.textContent : '';
        if (lbDesc)   lbDesc.textContent = descEl ? descEl.textContent : '';
        if (lbAuthor) lbAuthor.textContent = authorEl ? ('par ' + authorEl.textContent) : '';

        if (lightbox) lightbox.classList.add('cozy-lightbox--open');
        document.body.style.overflow = 'hidden';
    });

    // Fermer la lightbox
    function closeLightbox() {
        if (lightbox) lightbox.classList.remove('cozy-lightbox--open');
        document.body.style.overflow = '';
    }

    if (lightbox) {
        // Clic sur le fond
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) closeLightbox();
        });

        // Bouton fermer
        var closeBtn = document.getElementById('cozy-lightbox-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeLightbox);
        }
    }

    // Touche Echap
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLightbox();
    });

    /* ------------------------------------------------------------------
       5. SUPPRESSION PAR L'AUTEUR
       ------------------------------------------------------------------ */
    // Marquer les cartes de l'utilisateur connecté
    if (window.cozySetups && cozySetups.userId) {
        document.querySelectorAll('.cozy-setups__card').forEach(function (card) {
            // Vérifier data-author-id (ajouté côté PHP si on le souhaite)
            // Pour l'instant on ajoute le bouton supprimer côté JS pour les cartes data-own
        });
    }

    document.addEventListener('click', function (e) {
        var deleteBtn = e.target.closest('.cozy-setups__card-delete');
        if (!deleteBtn) return;

        e.preventDefault();

        if (!confirm('Supprimer ce setup ? Cette action est irréversible.')) return;

        var card = deleteBtn.closest('.cozy-setups__card');
        if (!card) return;

        var setupId = card.getAttribute('data-setup-id');
        if (!setupId || !window.cozySetups) return;

        var formData = new FormData();
        formData.append('action', 'cozy_delete_setup');
        formData.append('nonce', cozySetups.nonce);
        formData.append('setup_id', setupId);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', cozySetups.ajaxUrl, true);

        xhr.onload = function () {
            try {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    card.style.transition = 'opacity .3s, transform .3s';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(.9)';
                    setTimeout(function () { card.remove(); }, 350);
                } else {
                    alert(response.data.message || 'Erreur lors de la suppression.');
                }
            } catch (err) {
                alert('Erreur de communication.');
            }
        };

        xhr.send(formData);
    });

    /* ------------------------------------------------------------------
       UTILITAIRES
       ------------------------------------------------------------------ */
    function showMessage(text, type) {
        if (!messageEl) return;
        if (!text) {
            messageEl.style.display = 'none';
            messageEl.textContent = '';
            messageEl.className = 'cozy-setups__form-message';
            return;
        }
        messageEl.textContent = text;
        messageEl.className = 'cozy-setups__form-message cozy-setups__form-message--' + type;
        messageEl.style.display = 'block';
        messageEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /* ------------------------------------------------------------------
       VALIDATION — erreurs inline par champ
       ------------------------------------------------------------------ */
    function setFieldError(input, message) {
        if (!input) return;
        var group = input.closest('.cozy-setups__form-group');
        if (group) {
            group.classList.add('cozy-setups__form-group--error');
            if (!group.querySelector('.cozy-setups__field-error')) {
                var err = document.createElement('span');
                err.className = 'cozy-setups__field-error';
                err.textContent = message;
                group.appendChild(err);
            }
        }
        input.classList.add('cozy-setups__form-input--error');
    }

    function setUploadError(message) {
        if (dropzone) dropzone.classList.add('cozy-setups__upload-zone--error');
        var col = document.querySelector('.cozy-setups__form-upload-col');
        if (col && !col.querySelector('.cozy-setups__field-error')) {
            var err = document.createElement('span');
            err.className = 'cozy-setups__field-error';
            err.textContent = message;
            col.appendChild(err);
        }
    }

    function clearFieldError(input) {
        if (!input) return;
        var group = input.closest('.cozy-setups__form-group');
        if (group) {
            group.classList.remove('cozy-setups__form-group--error');
            var err = group.querySelector('.cozy-setups__field-error');
            if (err) err.remove();
        }
        input.classList.remove('cozy-setups__form-input--error');
    }

    function clearUploadError() {
        if (dropzone) dropzone.classList.remove('cozy-setups__upload-zone--error');
        var col = document.querySelector('.cozy-setups__form-upload-col');
        if (col) {
            var err = col.querySelector('.cozy-setups__field-error');
            if (err) err.remove();
        }
    }

    function clearAllErrors() {
        document.querySelectorAll('.cozy-setups__form-group--error').forEach(function (g) {
            g.classList.remove('cozy-setups__form-group--error');
        });
        document.querySelectorAll('.cozy-setups__field-error').forEach(function (e) {
            e.remove();
        });
        document.querySelectorAll('.cozy-setups__form-input--error').forEach(function (i) {
            i.classList.remove('cozy-setups__form-input--error');
        });
        clearUploadError();
    }

    // Effacer l'erreur du titre dès la saisie
    (function () {
        var titleInput = document.getElementById('cozy-setup-title');
        if (titleInput) {
            titleInput.addEventListener('input', function () {
                clearFieldError(titleInput);
            });
        }
    })();

    function resetForm() {
        if (!form) return;
        form.reset();
        // Masquer la preview et réafficher la dropzone
        if (previewZone) {
            previewZone.src = '';
            previewZone.style.display = 'none';
        }
        if (dropzone) {
            dropzone.style.display = '';
        }
        showMessage('', '');
    }

    /**
     * Réinitialise les icônes Lucide après insertion de HTML
     */
    function refreshIcons() {
        if (window.lucide && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    }

})();
