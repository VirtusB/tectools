window.addEventListener('load', e => {
    // JsBarcode laver vores stregkoder fra databasen om til SVG elementer som kan scannes
    JsBarcode(".barcode").init();
})

window.addEventListener('load', function () {
    handleExceededRentals();
    hideExceededReservations();

    var elems = document.querySelectorAll('.modal');
    var instances = M.Modal.init(elems);
});

/**
 * Tilføjer tooltips til udlejninger der er overskredet datoen for indlevering
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

    let form = `
    <form style="display: none;" method="POST">
        <input type="hidden" name="post_endpoint" value="deleteReservation">
        <input type="hidden" name="reservation_id" value="${id}">
    </form>
    `;

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
 * Åbner et vindue hvor brugeren kan indtaste en kommentar til deres udlejning
 * @param checkInID
 * @param context
 */
function showCommentCheckIn(checkInID, context) {
    let commentModal = M.Modal.getInstance(document.getElementById('comment-modal'));
    let commentTextArea = document.getElementById('comment-textarea');
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
            } else {
                if (!isAdmin()) {
                    commentTextArea.removeAttribute('readonly');
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
 * Gem en kommentar til en udlejning
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
 * Åbner et vindue hvor personale kan vælge status for værktøjet og tjekke det ud
 * @param checkInID
 * @param context
 */
function showCheckOutModal(checkInID, context) {
    let checkOutModal = M.Modal.getInstance(document.getElementById('check-out-modal'));
    let checkOutStatusSelect = document.getElementById('check-out-status-select');
    document.querySelector('#check-out-modal button').setAttribute('data-checkin-id', checkInID);

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
 * @param checkInID
 * @param context
 */
function checkOut(checkInID, context) {
    if (!confirm('Er du helt sikker?')) {
        return;
    }

    let statusID = context.parentElement.querySelector('#check-out-status-select').selectedOptions[0].value;

    let form = `
    <form style="display: none;" method="POST">
        <input type="hidden" name="post_endpoint" value="checkOut">
        <input type="hidden" name="check_in_id" value="${checkInID}">
        <input type="hidden" name="status_id" value="${statusID}">
    </form>
    `;

    $(context).append(form);
    $(context).find(`input[value=${checkInID}]`).parent().submit();
}
