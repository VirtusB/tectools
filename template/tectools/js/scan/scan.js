let scanBtn = document.getElementById('scan-btn');
let barcodeScanner = document.getElementById('barcode-scanner');
let scanContainer = document.getElementById('scan-container');
let toolContainer = document.getElementById('tool-container');

//TODO: tilføj kommentarer

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
    let storeLocations = JSON.parse(document.getElementById('store-locations-json').innerHTML.trim());

    Geofence.getUserLocation(() => {
        let userNearByAStore = Geofence.oneWithin(storeLocations, 1500);

        if (!userNearByAStore) {
            alert('Det ser ikke ud til, at du ikke er i nærheden af en af vores butikker. Du kan derfor ikke udlåne værktøjet på nuværende tidspunkt');
            return;
        }

        // Brugeren er i nærheden af en af vores butikker. Fortsæt udlåning.
        checkInTool(barcode);
    }, positionReadingError);
}

function positionReadingError() {
    alert('Vi kunne ikke finde dine koordinater. Sørg for at placeringstjenesten er aktiv på din smartphone, og at du har gives TecTools adgang til at bruge din lokation');
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
            if (res.result === 'success') {
                $(scanContainer).slideUp();

                toolContainer.querySelector('#check-in-btn').setAttribute('data-barcode', res.tool.BarCode);
                toolContainer.querySelector('#tool-name-manufacturer').innerText = res.tool.ManufacturerName + ' ' + res.tool.ToolName;
                toolContainer.querySelector('img').setAttribute('src', res.tool.Image);

                $(toolContainer).slideDown();
            } else {
                alert(res.result);
                console.error(res);
            }
        },
        error: function (err) {
            alert(err);
            console.error(err);
        }
    });
}

function disableCheckInBtn() {
    toolContainer.querySelector('#check-in-btn').setAttribute('disabled', 'disabled');
}

function enableCheckInBtn() {
    toolContainer.querySelector('#check-in-btn').removeAttribute('disabled');
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
            if (res.result === 'success') {
                NotificationControl.success('Udlånt', 'Værktøjet er nu udlånt til dig');
                showSuccessIcon('#check-in-btn');
            } else {
                NotificationControl.error('Fejl', res.result);
                document.getElementById('error-msg').innerText = res.result;
                document.getElementById('error-msg').style.display = 'initial';
                showErrorIcon('#check-in-btn');
            }
        },
        error: function (err) {
            alert(err);
        }
    });
}
