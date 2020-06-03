let barcodeInput = document.getElementById('barcode');
let toolNameInput = document.getElementById('tool_name');
let manufacturerSelect = document.getElementById('manufacturer_id');

window.addEventListener('load', e => {
   toolNameInput.addEventListener('keyup', nameOrManufacturerChanged);
   manufacturerSelect.addEventListener('change', nameOrManufacturerChanged);
});

function nameOrManufacturerChanged(event) {
    let barcode = generateBarCode();
    barcodeInput.value = barcode;
}

function generateBarCode() {
    let toolName = toolNameInput.value;
    let manufacturerName = manufacturerSelect.selectedOptions[0].innerText;

    let barcode = `${toolName} ${manufacturerName}`.replace(/\W+(?!$)/g, '_').replace(/\W$/, '').toLowerCase();

    let rand = getRandomInteger(5800, 9900);

    return barcode + '_' + rand;
}

