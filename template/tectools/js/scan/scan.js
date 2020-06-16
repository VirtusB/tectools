let scanBtn = document.getElementById('scan-btn');
let barcodeScanner = document.getElementById('barcode-scanner');

if (navigator.getUserMedia) {
    navigator.getUserMedia({video: true, audio: false}, addScanBtnClickListener, noVideoCameraError);
} else {
    noVideoCameraError();
}

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

Quagga.onDetected(function (data) {
    Quagga.stop();
    console.log(data);
    let code = data.codeResult.code;
    alert('Scannet: ' + code);
})
