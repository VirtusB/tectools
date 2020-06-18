let scanBtn = document.getElementById('scan-btn');
let barcodeScanner = document.getElementById('barcode-scanner');
let checkInButton = document.getElementById('check-in-btn');
let scanContainer = document.getElementById('scan-container');
let toolContainer = document.getElementById('tool-container');

//TODO: tilføj kommentarer

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

    let tool_barcode = data.codeResult.code;

    if (tool_barcode.length !== 13) {
        alert('FEJL: Længden af stregkoden er ikke 13 karakterer');
        location.reload();
    }

    showTool(tool_barcode);
});

function showTool(barcode) {
    $.post({
        url: location.origin + location.pathname,
        data: {'tool_barcode': barcode, 'get_tool_by_barcode_ajax': '1'},
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

function checkInTool(barcode) {
    toolContainer.querySelector('#check-in-btn').setAttribute('disabled', 'disabled');
    showLoader('#check-in-btn');

    $.post({
        url: location.origin + location.pathname,
        data: {'tool_barcode': barcode, 'check_in_tool': '1'},
        dataType: "json",
        cache: false,
        success: function(res) {
            if (res.result === 'success') {
                alert('Værktøjet er nu udlånt til dig');
                showSuccessIcon('#check-in-btn');
            } else {
                alert(res.result);
                showErrorIcon('#check-in-btn');
            }
        },
        error: function (err) {
            alert(err);
        }
    });
}
