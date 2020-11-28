/*
Denne fil indeholder klient kode som køres på siderne med hhv. personale manualen og bruger manualen
Den indeholder kode som vedrører normale bruger og personale brugere
Side: /personale-manual
Side: /bruger-manual
Layout: personale-manual.php
Layout: bruger-manual.php
 */

$('button.play').on('click', function () {
    $(this).next('.img-wrapper').slideToggle();
})