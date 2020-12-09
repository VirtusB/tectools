/*
Denne fil indeholder klient kode som køres på forsiden
Side: /
Layout: frontpage.php
 */

history.pushState(location.href,null, location.href.split('?')[0]);

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
 * Denne funktion sørger for at at alle de forskellige værktøjer på forsiden har samme højde
 */
function fixCardHeights() {
    let cardImages = document.querySelectorAll('#tools-row .card .card-image');
    let highestImage = 0;

    // Find højder
    cardImages.forEach(i => i.offsetHeight > highestImage ? highestImage = i.offsetHeight : highestImage = highestImage);

    // Sæt højder
    cardImages.forEach(i => i.style.height = highestImage + 'px');
}

/**
 * Viser/skjuler elementet "Seneste lån foretaget" på forsiden
 */
setInterval(function () {
    var marquees = Array.from(document.querySelectorAll('.marquee'));

    if (marquees.length === 0) {
        return;
    }

    var visibleMarquee = marquees.filter(el => el.style.display !== 'none')[0];
    var visibleMarqueeIndex = marquees.findIndex(m => m === visibleMarquee);

    animateCSS(visibleMarquee, 'backOutLeft', true).then(function () {
        var nextVisibleMarquee = marquees[visibleMarqueeIndex + 1];
        if (nextVisibleMarquee === undefined) {
            nextVisibleMarquee = marquees[0];
        }

        nextVisibleMarquee.style.display = 'block';
        animateCSS(nextVisibleMarquee, 'backInRight');
    });
}, 6500);