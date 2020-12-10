<?php

declare(strict_types=1);

?>

<style>
    .hero-container:before {
        background-image: url(<?= $this->RCMS->getTemplateFolder() ?>/images/faq-small.png);
    }
</style>

<div class="container">
    <div class="hero-container">

    </div>

    <div class="row">

        <div class="main-content contentContainer">
            <div class="contentArea contentPadding">

                <h2 class="heading3" id="general">Generelt</h2>

                <h3 class="toggle solid-border section-title-large">Hvordan...</h3>
                <div class="toggle-content" style="">
                    <p><b>Afleverer man et værktøj?</b></p>
                    <p>Når du er færdig med at benytte værktøjet, skal du aflevere det i et af varehusene, hvor personalet inspicerer værktøjet, læser din eventuelle kommentar og tjekker det ud af din bruger.</p>
                    <br> 
                    <p><b>Låner jeg et stykke værktøj?</b></p>
                    <p>Efter du har oprettet en bruger og købt et abonnement, skal du blot tage til et af Initechs fysiske varehuse, finde det værktøj du skal bruge, scanne det via checkin funktionen på hjemmesiden via din mobil og tage værktøjet med hjem.</p> 
                    <br> 
                    <p><b>Gør jeg x?</b></p>
                    <p>Se vores <a href="https://tectools.virtusb.com/bruger-manual">manual</a> for korte video instruktioner til brugen af hjemmesiden (link)</p>
                    <br> 
                    <p><b>Skal jeg gøre hvis jeg oplever en fejl på siden</b></p>
                    <p>I tilfælde af fejl bedes du sende en besked med en god beskrivelse af fejlen via vores <a href="https://tectools.virtusb.com/contact">kontakt-side</a></p>
                </div>

                <h3 class="toggle solid-border section-title-large">Hvor...</h3>
                <div class="toggle-content" style="">
                    <p><b>Kan jeg låne værktøj?</b></p>
                    <p>Du kan låne værktøj i et af Initechs fysiske varehuse</p>
                    <br>
                    <p><b>Kan jeg aflevere værktøj?</b></p>
                    <p>Du kan aflevere værktøj i et af Initechs fysiske varehuse</p>
                    <br>
                    <p><b>Blev min reservation af?</b></p>
                    <p>En reservation udløber efter 24 eller 48 timer afhængig af abonnement, da værktøjet også skal være tilgængeligt for andre. Husk at låne det i denne tidsperiode. Du kan se dine reservationer på dit <a href="https://tectools.virtusb.com/dashboard#reservations-tab">dashboard</a></p>
                </div>

                <h3 class="toggle solid-border section-title-large">Hvad...</h3>
                <div class="toggle-content" style="">
                    <p><b>Gør kommentar-feltet?</b></p>
                    <p>Kommentar-feltet lader dig skrive en kort besked til Initechs personale. 
                    <p>Dette kunne fx være en kommentar til værktøjets tilstand ved checkin/checkud eller begrundelse for eventuelle skader.</p>
                    <p>Fx kan du skrive i dette felt, hvis en Trillebørs dæk er punkteret, en rundsavs klinge er slidt eller et par sko mangler såler.</p>
                    <p>Denne kommentar vil Initechs personale læse, og fx sende værktøjet til reparation. </p>
                </div>

                <h3 class="toggle solid-border section-title-large">Hvorfor...</h3>
                <div class="toggle-content" style="">
                    <p><b>Kan jeg ikke reservere flere stykker værktøj?</b></p>
                    <p>Dette kan være fordi dit abonnement ikke tillader flere, værktøjet allerede er reserveret eller at værktøjet ikke er på lager</p>
                    <br>
                    <p><b>Fik jeg en bøde</b></p>
                    <p>Du kan få en bøde, hvis du ikke afleverer til tiden eller hvis du har slemt misvedligeholdt et stykke lånt værktøj. Bøden afspejler hvor sent værktøjet blev afleveret eller graden af skaden på værktøjet</p>
                    <br>
                    <p><b>Har I ikke værktøjet x?</b></p>
                    <p>Hvis du mener at vi mangler et stykke værktøj i vores kartotek, bedes du sende en besked med hvilket værktøj du ønsker tilføjet via vores <a href="https://tectools.virtusb.com/contact">kontakt-side</a></p>
                </div>

            </div>
        </div>
    </div>

</div>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/faq.css">

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/faq/faq.js"></script>




