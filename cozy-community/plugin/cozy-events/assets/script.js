/**
 * ============================================================================
 * COZY EVENTS — Script Front-end (v1.1)
 * ============================================================================
 *
 * Inscription / désinscription AJAX + acceptation de la charte.
 */

jQuery(function ($) {
  'use strict';

  var msg = $('#cozy-registration-message');

  // ── Helpers ──
  function showMsg(text, success) {
    msg.text(text)
       .removeClass('cozy-registration-box__message--success cozy-registration-box__message--error')
       .addClass(success ? 'cozy-registration-box__message--success' : 'cozy-registration-box__message--error')
       .show();
  }

  // Init Lucide icons (si disponible)
  function refreshIcons() {
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  }

  // ── Charte : checkbox → active le bouton ──
  $(document).on('change', '#cozy-charter-checkbox', function () {
    $('#cozy-accept-charter-btn').prop('disabled', !this.checked);
  });

  // ── Charte : accepter ──
  $(document).on('click', '#cozy-accept-charter-btn', function () {
    var btn = $(this);
    if (btn.prop('disabled')) return;

    btn.prop('disabled', true).html('<i data-lucide="loader"></i> Validation...');

    $.post(cozyEvents.ajax_url, {
      action: 'cozy_accept_charter',
      nonce:  cozyEvents.nonce,
    })
    .done(function (res) {
      if (res.success) {
        // Recharger la page pour afficher le formulaire d'inscription
        location.reload();
      } else {
        showMsg(res.data.message, false);
        btn.prop('disabled', false).html('<i data-lucide="check"></i> Accepter et continuer');
        refreshIcons();
      }
    })
    .fail(function () {
      showMsg('Une erreur est survenue. Réessayez.', false);
      btn.prop('disabled', false).html('<i data-lucide="check"></i> Accepter et continuer');
      refreshIcons();
    });
  });

  // ── Inscription ──
  $(document).on('click', '#cozy-register-btn', function () {
    var btn      = $(this);
    var eventId  = btn.data('event-id');
    var trocNote = $('#cozy-troc-note').val() || '';

    btn.prop('disabled', true).html('<i data-lucide="loader"></i> Inscription en cours...');

    $.post(cozyEvents.ajax_url, {
      action:    'cozy_register',
      nonce:     cozyEvents.nonce,
      event_id:  eventId,
      troc_note: trocNote,
    })
    .done(function (res) {
      if (res.success) {
        showMsg(res.data.message, true);
        location.reload();
      } else {
        showMsg(res.data.message, false);
        btn.prop('disabled', false).html('<i data-lucide="sparkles"></i> M\'inscrire');
        refreshIcons();
      }
    })
    .fail(function () {
      showMsg('Une erreur est survenue. Réessayez.', false);
      btn.prop('disabled', false).html('<i data-lucide="sparkles"></i> M\'inscrire');
      refreshIcons();
    });
  });

  // ── Désinscription ──
  $(document).on('click', '#cozy-unregister-btn', function () {
    if (!confirm('Confirmer la désinscription ?')) return;

    var btn     = $(this);
    var eventId = btn.data('event-id');

    btn.prop('disabled', true).html('<i data-lucide="loader"></i> Désinscription...');

    $.post(cozyEvents.ajax_url, {
      action:   'cozy_unregister',
      nonce:    cozyEvents.nonce,
      event_id: eventId,
    })
    .done(function (res) {
      if (res.success) {
        showMsg(res.data.message, true);
        location.reload();
      } else {
        showMsg(res.data.message, false);
        btn.prop('disabled', false).html('<i data-lucide="x"></i> Me désinscrire');
        refreshIcons();
      }
    })
    .fail(function () {
      showMsg('Une erreur est survenue. Réessayez.', false);
      btn.prop('disabled', false).html('<i data-lucide="x"></i> Me désinscrire');
      refreshIcons();
    });
  });
});
