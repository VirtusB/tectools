window.addEventListener('load', e => {
    JsBarcode(".barcode").init();

    window.timeagoInstance.render(document.querySelectorAll('.render-datetime'), 'da_DK');
})


