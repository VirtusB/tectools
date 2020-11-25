let scanBtn = document.getElementById('scan-btn');
let barcodeScanner = document.getElementById('barcode-scanner');
let scanContainer = document.getElementById('scan-container');
let toolContainer = document.getElementById('tool-container');

// Tjek om browseren har mulighed for at åbne en video-stream
if (navigator.getUserMedia) {
    navigator.mediaDevices.getUserMedia({video: true, audio: false}).then(addScanBtnClickListener, noVideoCameraError);
} else {
    noVideoCameraError();
}

/**
 * Hvis browseren ikke understøtter video-streaming får brugeren en fejl
 */
function noVideoCameraError() {
    alert('Din enhed eller browser understøtter ikke scanning');
    scanBtn.setAttribute('disabled', 'disabled');
}

function addScanBtnClickListener(stream) {
    stream.getTracks().forEach(t => t.stop()); // For at lukke det track, som vi brugte, til at tjekke om brugeren har et kamera
    scanBtn.addEventListener('click', scanBtnClickHandler, {once: true});
}

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
            alert('Fejl: ' + err);
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
        alert('FEJL: Længden af stregkoden er ikke 13 karakterer');
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
        data: {'tool_barcode': barcode, 'post_endpoint': 'getToolByBarcodeAjax'},
        dataType: "json",
        cache: false,
        success: function(res) {
            $(scanContainer).slideUp();

            toolContainer.querySelector('#check-in-btn').setAttribute('data-barcode', res.tool.BarCode);
            toolContainer.querySelector('#tool-name-manufacturer').innerText = res.tool.ManufacturerName + ' ' + res.tool.ToolName;
            toolContainer.querySelector('img').setAttribute('src', res.tool.Image);

            $(toolContainer).slideDown();
        },
        error: function (err) {
            NotificationControl.error('Fejl', err.responseJSON.result);
        }
    });
}

/**
 * Sender en AJAX request til serveren og udlåner et værktøj til brugeren
 * @param {string} barcode
 */
function checkInTool(barcode) {
    toolContainer.querySelector('#check-in-btn').setAttribute('disabled', 'disabled');
    showLoader('#check-in-btn');

    $.post({
        url: location.origin + location.pathname,
        data: {'tool_barcode': barcode, 'post_endpoint': 'checkIn'},
        dataType: "json",
        cache: false,
        success: function(res) {
            NotificationControl.success('Udlånt', 'Værktøjet er nu udlånt til dig');
            showSuccessIcon('#check-in-btn', 'Udlånt');
        },
        error: function (err) {
            NotificationControl.error('Fejl', err.responseJSON.result);
            document.getElementById('error-msg').innerText = err.responseJSON.result;
            document.getElementById('error-msg').style.display = 'initial';
            showErrorIcon('#check-in-btn', 'Fejl');
        }
    });
}


