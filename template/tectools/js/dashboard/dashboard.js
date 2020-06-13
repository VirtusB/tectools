window.addEventListener('load', e => {
    JsBarcode(".barcode").init();

    window.timeagoInstance.render(document.querySelectorAll('.check-in-out-date'), 'da_DK');
})


