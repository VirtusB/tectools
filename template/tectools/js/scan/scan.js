/*
Denne fil indeholder klient kode som køres på Scan siden
Den indeholder kode som vedrører normale bruger og personale brugere
Side: /scan
Layout: scan.php
 */

let scanBtn = document.getElementById('scan-btn');
let barcodeScanner = document.getElementById('barcode-scanner');
let scanContainer = document.getElementById('scan-container');
let toolContainer = document.getElementById('tool-container');
let toolContainerAdmin = document.getElementById('tool-container-admin');
let checkInBtn = document.getElementById('check-in-btn');

// Tjek om browseren har mulighed for at åbne en video-stream
if (navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices.getUserMedia({video: true, audio: false}).then(addScanBtnClickListener, noVideoCameraError);
} else {
    noVideoCameraError();
}

/**
 * Denne funktion køre, hvis browseren IKKE understøtter video-streaming
 */
function noVideoCameraError(err) {
    console.log(err)
    alert('Din enhed eller browser understøtter ikke scanning');
    scanBtn.setAttribute('disabled', 'disabled');
}

/**
 * Denne funktion køre, hvis browseren understøtter video-streaming
 * @param stream
 */
function addScanBtnClickListener(stream) {
    stream.getTracks().forEach(t => t.stop()); // For at lukke det track, som vi brugte, til at tjekke om brugeren har et kamera
    scanBtn.addEventListener('click', scanBtnClickHandler);
}

/**
 * Denne funktion køres når brugeren klikker på "Scan" knappen
 * @param event
 */
function scanBtnClickHandler(event) {
    startScan();
    barcodeScanner.style.border = 'none';
}

/**
 * Kontrollerer, at brugeren er inden for en radius på 1500 meter fra en af vores butikker
 * 1500 meter er en stor og gavmild radius, men er nødvendig da Geolocation API'et ikke er 100% præcis
 */
function startGeofence(barcode) {
    // Ex. [{lat: 55.12, long: 12.6}, {lat: 54.24, long: 12.9}]
    let storeLocations = JSON.parse(document.getElementById('store-locations-json').innerHTML.trim());

    Geofence.getUserLocation(() => {
        let isUserNearbyAStore = Geofence.oneWithinRadius(storeLocations, 1500);

        if (!isUserNearbyAStore) {
            alert('Det ser ikke ud til, at du ikke er i nærheden af en af vores butikker. Du kan derfor ikke udlåne værktøjet på nuværende tidspunkt');
            return;
        }

        // Brugeren er i nærheden af en af vores butikker. Fortsæt udlåning.
        checkInTool(barcode);
    }, () => alert('Vi kunne ikke finde dine koordinater. Sørg for at placeringstjenesten er aktiv på din smartphone, og at du har gives TecTools adgang til at bruge din lokation'));
}

/**
 * Åbner video-streaming på enheden og starter Quagga
 * Quagga konfigureres til at scanne EAN stregkoder
 */
function startScan() {
    Quagga.init({
        inputStream : {
            name : "Live",
            type : "LiveStream",
            target: document.querySelector('#barcode-scanner')
        },
        decoder : {
            readers : ["ean_reader"]
        }
    }, function(err) {
        if (err) {
            console.log(err);
            NotificationControl.error('Fejl', err);
            return;
        }

        console.log("Klar til at starte scanneren");
        Quagga.start();
    });
}

/**
 * Denne funktion kører når Quagga har fundet en stregkode
 */
Quagga.onDetected(function (data) {
    Quagga.stop();

    let tool_barcode = data.codeResult.code;

    if (tool_barcode.length !== 13) {
        NotificationControl.error('Fejl', 'Længden af stregkoden er ikke 13 karakterer')
        location.reload();
    }

    showTool(tool_barcode);
});

/**
 * Henter et værktøj via AJAX og viser værktøjet på siden
 * @param {string} barcode
 */
function showTool(barcode) {
    $.post({
        url: location.origin + location.pathname,
        data: {'tool_barcode': barcode, 'post_endpoint': isAdmin() ? 'getCheckInAjax' : 'getToolByBarcodeAjax'},
        dataType: "json",
        cache: false,
        success: function(res) {
            $(scanContainer).slideUp();

            if (isAdmin()) {
                toolContainerAdmin.querySelector('#check-out-btn').setAttribute('data-checkin-id', res.result.CheckInID);
                toolContainerAdmin.querySelector('#tool-name-manufacturer-admin').innerText = res.result.tool.ManufacturerName + ' ' + res.result.tool.ToolName;
                toolContainerAdmin.querySelector('img').setAttribute('src', res.result.tool.Image);
                toolContainerAdmin.querySelector('img').style.maxWidth = '50%';

                toolContainerAdmin.querySelector('#user-id').innerHTML = `<a href="/users/edit?userid=${res.result.FK_UserID}" target="_blank">${res.result.FK_UserID}</a>`
                toolContainerAdmin.querySelector('#start-date').innerText = res.result.formattedStartDate;
                toolContainerAdmin.querySelector('#end-date').innerText = res.result.formattedEndDate;
                toolContainerAdmin.querySelector('#comment').innerText = res.result.Comment;

                $(toolContainerAdmin).slideDown();
            } else {
                toolContainer.querySelector('#check-in-btn').setAttribute('data-barcode', res.result.BarCode);
                toolContainer.querySelector('#tool-name-manufacturer').innerText = res.result.ManufacturerName + ' ' + res.result.ToolName;
                toolContainer.querySelector('img').setAttribute('src', res.result.Image);

                $(toolContainer).slideDown();
            }
        },
        error: function (err) {
            NotificationControl.error('Fejl', err.responseJSON.result);
        }
    });
}

/**
 * Deaktiverer "Lån" knappen og gør at brugeren ikke kan klikke på den
 */
function disableCheckInBtn() {
    checkInBtn.setAttribute('disabled', 'disabled');
}

/**
 * Aktiverer "Lån" knappen og gør at brugeren kan klikke på den
 */
function enableCheckInBtn() {
    checkInBtn.removeAttribute('disabled');
}

/**
 * Sender en AJAX request til serveren og udlåner et værktøj til brugeren
 * @param {string} barcode
 */
function checkInTool(barcode) {
    disableCheckInBtn();
    showLoader('#check-in-btn');

    $.post({
        url: location.origin + location.pathname,
        data: {'tool_barcode': barcode, 'post_endpoint': 'checkIn'},
        dataType: "json",
        cache: false,
        success: function(res) {
            NotificationControl.success('Udlånt', 'Værktøjet er nu udlånt til dig');
            showSuccessIcon('#check-in-btn', 'Udlånt');
            setTimeout(afterCheckIn, 3200);
        },
        error: function (err) {
            NotificationControl.error('Fejl', err.responseJSON.result);
            document.getElementById('error-msg').innerText = err.responseJSON.result;
            document.getElementById('error-msg').style.display = 'initial';
            showErrorIcon('#check-in-btn', 'Fejl');
        }
    });
}

/**
 * Personale på Scan siden får vist en "Tjek Ud" knap, i stedet for en "Lån" knap
 * Når personalet klikker "Tjek Ud", køre denne funktion
 * @param checkInID
 */
function checkOutTool(checkInID) {
    let statusID = document.getElementById('tool-status').selectedOptions[0].value;

    let form = `
    <form id="check-out-tool-form" style="display: none;" method="POST">
        <input type="hidden" name="post_endpoint" value="checkOut">
        <input type="hidden" name="check_in_id" value="${checkInID}">
        <input type="hidden" name="status_id" value="${statusID}">
    </form>
    `;

    $(document.body).append(form);
    $('#check-out-tool-form').submit();
}

/**
 * Funktion som kører efter brugeren har lånt et værktøj
 * Den genstarter processen, så brugeren kan låne flere værktøj kort tid efter hinanden på samme side
 */
function afterCheckIn() {
    if (!isAdmin()) {
        $(toolContainer).slideUp();
        enableCheckInBtn();
        $(checkInBtn).html(`Lån   <i class="fal fa-shopping-basket"></i>`);
        $(scanContainer).slideDown();
    }
}