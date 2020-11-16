window.addEventListener('load', e => {
    // JsBarcode laver vores stregkoder fra databasen om til SVG elementer som kan scannes
    JsBarcode(".barcode").init();

    // Biblioteket "timeago.js" formaterer datoer som f.eks. "2020-01-01" til "1. januar 2020"
    window.timeagoInstance.render(document.querySelectorAll('.render-datetime'), 'da_DK');
})

window.addEventListener('load', function () {
    handleExceededRentals();
});

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


