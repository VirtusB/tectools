document.addEventListener('DOMContentLoaded', function() {
    let categorySelect = document.getElementById('category-select');

    let instance = M.FormSelect.init(categorySelect);

    let height = $('#category-select-col').height();

    $('#category-select-col').next().height(height);

});

window.addEventListener('load', function () {
    fixCardHeights();

    /**
     * setTimeout bruges så vi kan bestemme hvor ofte fixCardHeights funktionen kan køres.
     * Når man ændre størrelse på browservinduet bliver "resize" eventet triggered flere 100 gange i sekundet.
     * Der er ikke nogen grund til at køre fixCardHeights så ofte, så vi begrænser det til 4 gange i sekundet.
     */
    var resizeTimer;
    window.addEventListener('resize', function (e) {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            fixCardHeights();
        }, 250);
    });
});

/**
 * Denne funktion søger for at at alle de forskellige værktøjer på forsiden har samme højde
 */
function fixCardHeights() {
    let cards = document.querySelectorAll('#tools-row .card');
    let cardImages = document.querySelectorAll('#tools-row .card .card-image');
    let cardActions = document.querySelectorAll('#tools-row .card-action');

    let highestCard = 0;
    let highestImage = 0;
    let highestAction = 0;

    // Find højder
    cards.forEach(c => c.offsetHeight > highestCard ? highestCard = c.offsetHeight : highestCard = highestCard);
    cardImages.forEach(i => i.offsetHeight > highestImage ? highestImage = i.offsetHeight : highestImage = highestImage);
    cardActions.forEach(i => i.offsetHeight > highestAction ? highestAction = i.offsetHeight : highestAction = highestAction);

    // Sæt højder
    // cards.forEach(c => c.style.height = highestCard + 'px');
    cardImages.forEach(i => i.style.height = highestImage + 'px');
    // cardActions.forEach(i => i.style.height = highestAction + 'px');
}