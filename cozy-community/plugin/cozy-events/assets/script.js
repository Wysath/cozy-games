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


/* ============================================================================
 * COZY EVENTS — Calendrier Mensuel  (v1.3)
 * ============================================================================
 *
 * Vue calendrier pour la page d'archive des événements.
 * Lit les données injectées par wp_localize_script (cozyCalendar).
 */
(function () {
    'use strict';

    if (typeof cozyCalendar === 'undefined') return;

    var calendarEl = document.getElementById('cozy-events-calendar');
    if (!calendarEl) return;

    var events       = cozyCalendar.events || [];
    var now          = new Date();
    var currentMonth = now.getMonth();
    var currentYear  = now.getFullYear();

    var monthNames = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ];
    var dayNames = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    /* ── Rendu du calendrier ── */
    function renderCalendar() {
        var titleEl = document.getElementById('cozy-cal-title');
        var gridEl  = document.getElementById('cozy-cal-grid');
        if (!titleEl || !gridEl) return;

        titleEl.textContent = monthNames[currentMonth] + ' ' + currentYear;
        gridEl.innerHTML = '';

        // En-têtes jours
        dayNames.forEach(function (name) {
            var el = document.createElement('div');
            el.className = 'cozy-calendar__day-name';
            el.textContent = name;
            gridEl.appendChild(el);
        });

        var firstDay    = new Date(currentYear, currentMonth, 1);
        var lastDay     = new Date(currentYear, currentMonth + 1, 0);
        var startOffset = (firstDay.getDay() + 6) % 7; // Lundi = 0
        var daysInMonth = lastDay.getDate();

        var todayStr = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate());

        // Cellules vides avant le 1er
        for (var e = 0; e < startOffset; e++) {
            var empty = document.createElement('div');
            empty.className = 'cozy-calendar__day cozy-calendar__day--empty';
            gridEl.appendChild(empty);
        }

        // Jours du mois
        for (var d = 1; d <= daysInMonth; d++) {
            var dateStr = currentYear + '-' + pad(currentMonth + 1) + '-' + pad(d);
            var dayEl   = document.createElement('div');
            dayEl.className = 'cozy-calendar__day';

            if (dateStr === todayStr) {
                dayEl.classList.add('cozy-calendar__day--today');
            }
            if (dateStr < todayStr) {
                dayEl.classList.add('cozy-calendar__day--past');
            }

            var numEl = document.createElement('span');
            numEl.className = 'cozy-calendar__day-number';
            numEl.textContent = d;
            dayEl.appendChild(numEl);

            // Événements de ce jour
            var dayEvents = events.filter(function (ev) { return ev.date === dateStr; });

            dayEvents.forEach(function (ev) {
                var a = document.createElement('a');
                a.href      = ev.url;
                a.className = 'cozy-calendar__event';

                if (ev.isTroc)          a.classList.add('cozy-calendar__event--troc');
                if (ev.placesLeft === 0) a.classList.add('cozy-calendar__event--full');

                var label = '';
                if (ev.time) label += ev.time + ' · ';
                label += ev.title;
                a.textContent = label;
                a.title       = ev.title + (ev.game ? ' — ' + ev.game : '');

                dayEl.appendChild(a);
            });

            if (dayEvents.length > 0) {
                dayEl.classList.add('cozy-calendar__day--has-events');
            }

            gridEl.appendChild(dayEl);
        }

        // Rafraîchir les icônes Lucide
        if (window.lucide && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    }

    /* ── Navigation mois ── */
    var prevBtn = document.getElementById('cozy-cal-prev');
    var nextBtn = document.getElementById('cozy-cal-next');

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            renderCalendar();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            renderCalendar();
        });
    }

    /* ── Bascule liste / calendrier ── */
    var viewBtns = document.querySelectorAll('.cozy-events-view-btn');

    viewBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var view = this.getAttribute('data-view');

            viewBtns.forEach(function (b) { b.classList.remove('cozy-events-view-btn--active'); });
            this.classList.add('cozy-events-view-btn--active');

            document.querySelectorAll('.cozy-events-view').forEach(function (v) {
                v.style.display = 'none';
            });

            var target = document.getElementById('cozy-events-' + view);
            if (target) target.style.display = 'block';

            if (view === 'calendar') renderCalendar();
        });
    });

})();
