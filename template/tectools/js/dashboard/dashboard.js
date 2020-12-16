/*
Denne fil indeholder klient kode som køres på Dashboard siden
Den indeholder kode som vedrører normale bruger og personale brugere
Side: /dashboard
Layout: dashboard.php
 */

$(document).ready(function(){
    $('.tabs').tabs();

    if (location.hash !== '') {
        $('.tabs').tabs('select', location.hash);
    }

    $('a[data-toggle="tab"]').click(function(e) {
        if (history.replaceState) {
            history.replaceState(null, null, '#' + $(e.target).attr('href').substr(1));
        } else {
            location.hash = '#' + $(e.target).attr('href').substr(1);
        }

        $(window).trigger('hashchange');
    });

    if (!isAdmin()) {
        addDashboardTooltip();
    }
});

/**
 * Lukker tooltip til tab-menuen på dashboard siden
 * @param instance
 */
function closeTooltip(instance) {
    instance[0].destroy();
    setCookie('seen_dashboard_tooltip', 'true', 3650);
}

/**
 * Tilføjer et tooltip til tab-menuen på dashboard siden
 */
function addDashboardTooltip() {
    if (getCookie('seen_dashboard_tooltip') === 'true') {
        return;
    }

    let t =
        `
        <div style="user-select: none; max-width: 150px">
            <h6 class="center">Menu</h6>

            <p class="center">Klik på et af disse punkter for at se mere information</p>
            <button id="closeTooltip" style="background: #0c8dff; width: 100%" class="btn">OK</button>
        </div>
        `;

    let instance = tippy('#dashboard-tabs', {
        content: t,
        trigger: 'manual',
        // placement: 'top-start',
        interactive: true,
        allowHTML: true,
        hideOnClick: false,
        showOnCreate: true,
        onMount: () => {
            document.getElementById('closeTooltip').addEventListener('click', function () {
               closeTooltip(instance);
            });
        }
    });
}

window.addEventListener('load', e => {
    // JsBarcode laver vores stregkoder fra databasen om til SVG elementer som kan scannes
    JsBarcode(".barcode").init();
})

window.addEventListener('load', function () {
    handleExceededRentals();
    hideExceededReservations();
    disablePaidFines();

    var elems = document.querySelectorAll('.modal');
    var instances = M.Modal.init(elems);
});

/**
 * Tilføjer tooltips til lån der har overskredet datoen for indlevering
 */
function handleExceededRentals() {
    let elements = document.querySelectorAll('td[data-exceeded-date="1"]');

    elements.forEach(el => {
        if (!el.classList.contains('tooltipped')) {
            el.classList.add('tooltipped');
            el.setAttribute('data-position', 'left');
            el.setAttribute('data-tooltip', 'Overskredet');
        }
    });

    let elems = document.querySelectorAll('.tooltipped');
    let instances = M.Tooltip.init(elems);
}

/**
 * Sletter en reservation
 * @param id
 */
function deleteReservation(id, context) {
    if (!confirm('Er du helt sikker?')) {
        return;
    }

    // "id" variablen er reservations ID'et, hvis det er brugeren selv der sletter reservationen
    // "id" variablen er et bruger ID, hvis det er en personale bruger der sletter en reservation som tilhører en bruger

    let url = location.pathname + window.location.hash;

    if (isAdmin()) {
        let reservationID = $(context).parent().parent().find('td').first().html();

        var form = `
        <form style="display: none;" method="POST">
            <input type="hidden" name="post_endpoint" value="deleteReservation">
            <input type="hidden" name="user_id" value="${id}">
            <input type="hidden" name="reservation_id" value="${reservationID}">
            <input type="hidden" name="custom_location" value="${url}">
        </form>
        `;
    } else {
        var form = `
        <form style="display: none;" method="POST">
            <input type="hidden" name="post_endpoint" value="deleteReservation">
            <input type="hidden" name="reservation_id" value="${id}">
            <input type="hidden" name="custom_location" value="${url}">
        </form>
        `;
    }

    $(context).append(form);
    $(context).find(`input[value=${id}]`).parent().submit();
}

/**
 * Gemmer reservationer i samme sekund som reservationen udløber
 */
function hideExceededReservations() {
    var si = setInterval(function () {
        let table = document.getElementById('reservations_table');

        if (table === null) {
            clearInterval(si);
            return;
        }

        table.querySelectorAll('tr').forEach(tr => {
            let datetimeTds = tr.querySelectorAll('td[datetime]')

            if (datetimeTds.length !== 0) {
                let dateTime = datetimeTds[1].getAttribute('datetime');
                if (Date.parse(dateTime) - Date.parse(new Date()) < 0) {
                    tr.remove();
                }
            }
        });
    }, 500);
}

/**
 * Denne funktion deaktiverer knappen "Betal bøde" hvis bøden allerede er betalt
 */
function disablePaidFines() {
    let elements = document.querySelectorAll('td[data-is-paid="1"]');

    elements.forEach(el => {
        let btn = el.parentElement.querySelector('button.pay-fine-btn');
        btn.setAttribute('disabled', 'disabled');
    });
}

/**
 * Åbner et vindue hvor brugeren kan indtaste en kommentar til deres lån
 * @param checkInID
 * @param context
 */
function showCommentCheckIn(checkInID, context) {
    let commentModal = M.Modal.getInstance(document.getElementById('comment-modal'));
    let commentTextArea = document.getElementById('comment-textarea');
    let btn = document.getElementById('comment-modal').querySelector('button');
    document.querySelector('#comment-modal button').setAttribute('data-checkin-id', checkInID);

    $.post({
        url: location.origin + location.pathname,
        data: {'check_in_id': checkInID, 'post_endpoint': 'getCheckInComment'},
        dataType: "json",
        cache: false,
        success: function(res) {
            commentTextArea.value = res.result.Comment;

            if (res.result.CheckedOut === 1) {
                commentTextArea.setAttribute('readonly', 'readonly');
                btn.innerText = 'Luk';
                btn.onclick = '';
            } else {
                if (!isAdmin()) {
                    commentTextArea.removeAttribute('readonly');
                    btn.innerText = 'Gem';
                    btn.onclick = function () {
                        saveCheckInComment(btn.getAttribute('data-checkin-id'), btn)
                    };
                }
            }

            commentModal.open();
        },
        error: function (err) {
            NotificationControl.error('Fejl', err.responseJSON.result);
        }
    });
}

/**
 * Gemmer en kommentar til et lån
 * @param checkInID
 * @param context
 */
function saveCheckInComment(checkInID, context) {
    let comment = document.getElementById('comment-textarea').value;

    $.post({
        url: location.origin + location.pathname,
        data: {'check_in_id': checkInID, 'post_endpoint': 'saveCheckInComment', 'comment': comment},
        dataType: "json",
        cache: false,
        success: function(res) {
            NotificationControl.success('Gemt', 'Kommentaren blev gemt');
        },
        error: function (err) {
            NotificationControl.error('Fejl', err.responseJSON.result);
        }
    });
}

/**
 * Åbner et vindue hvor personale kan vælge status for et værktøj og tjekke det ud
 * @param checkInID
 * @param context
 */
function showCheckOutModal(checkInID, context) {
    let checkOutModal = M.Modal.getInstance(document.getElementById('check-out-modal'));
    let checkOutStatusSelect = document.getElementById('check-out-status-select');
    let fineContainer = document.getElementById('fine-container');
    let fineAmountInput = document.getElementById('fine-amount');
    let fineCommentTextarea = document.getElementById('fine-comment');
    let addFineCheckbox = document.getElementById('add-fine-checkbox');

    // Reset inputs
    document.querySelector('#check-out-modal button').setAttribute('data-checkin-id', checkInID);
    fineAmountInput.setAttribute('disabled', 'disabled');
    fineCommentTextarea.setAttribute('disabled', 'disabled');
    fineAmountInput.value = 0;
    fineCommentTextarea.value = '';
    checkOutStatusSelect.value = 1;
    checkOutStatusSelect.dispatchEvent(new Event('change'));
    addFineCheckbox.checked = false;
    fineContainer.style.opacity = '0.3';

    $.post({
        url: location.origin + location.pathname,
        data: {'check_in_id': checkInID, 'post_endpoint': 'getCheckInAjax'},
        dataType: "json",
        cache: false,
        success: function(res) {
            checkOutModal.open();
        },
        error: function (err) {
            NotificationControl.error('Fejl', err.responseJSON.result);
        }
    });
}

/**
 * Tjekker et værktøj ud
 * Når personalet klikker "Tjek Ud", køre denne funktion
 * @param checkInID
 * @param context
 */
function checkOut(checkInID, context) {
    if (!confirm('Er du helt sikker?')) {
        return;
    }

    let checkOutStatusSelect = document.getElementById('check-out-status-select');
    let fineAmountInput = document.getElementById('fine-amount');
    let fineCommentTextarea = document.getElementById('fine-comment');
    let addFineCheckbox = document.getElementById('add-fine-checkbox');

    let statusID = checkOutStatusSelect.selectedOptions[0].value;
    let fineAmount = +fineAmountInput.value;
    let fineComment = fineCommentTextarea.value;
    let shouldAddFine = addFineCheckbox.checked && fineAmount !== 0;
    let url = location.pathname + window.location.hash;

    let form = `
    <form style="display: none;" method="POST">
        <input type="hidden" name="post_endpoint" value="checkOut">
        <input type="hidden" name="check_in_id" value="${checkInID}">
        <input type="hidden" name="status_id" value="${statusID}">
        <input type="hidden" name="custom_location" value="${url}">
        ${shouldAddFine ? `<input type="hidden" name="fine_amount" value="${fineAmount}">` : ''}
        ${shouldAddFine ? `<input type="hidden" name="fine_comment" value="${fineComment}">` : ''}
    </form>
    `;

    $(context).append(form);
    $(context).find(`input[value=${checkInID}]`).parent().submit();
}

/**
 * Denne funktion kører, når tjekboksen "Tilføj bøde?" ændres
 * @param context
 */
function fineCheckBoxChange(context) {
    toggleAttr('#fine-amount', 'disabled', 'disabled');
    toggleAttr('#fine-comment', 'disabled', 'disabled');
    let fineContainer = document.getElementById('fine-container');

    if (context.checked) {
        fineContainer.style.opacity = '1';
    } else {
        fineContainer.style.opacity = '0.3';
    }
}

/**
 * Slet producent
 * @param manufacturerID
 * @param context
 */
function deleteManufacturer(manufacturerID, context) {
    if (!confirm('Er du helt sikker?')) {
        return;
    }

    let form = `
    <form style="display: none;" method="POST">
        <input type="hidden" name="post_endpoint" value="deleteManufacturer">
        <input type="hidden" name="manufacturer_id" value="${manufacturerID}">
    </form>
    `;

    $(context).append(form);
    $(context).find(`input[value=${manufacturerID}]`).parent().submit();
}

/**
 * Slet kategori
 * @param categoryID
 * @param context
 */
function deleteCategory(categoryID, context) {
    if (!confirm('Er du helt sikker?')) {
        return;
    }

    let form = `
    <form style="display: none;" method="POST">
        <input type="hidden" name="post_endpoint" value="deleteCategory">
        <input type="hidden" name="category_id" value="${categoryID}">
    </form>
    `;

    $(context).append(form);
    $(context).find(`input[value=${categoryID}]`).parent().submit();
}