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
                    <p><span class="bold">Afleverer jeg et stykke værktøj?</span></p>
                    <p>Når du er færdig med at benytte værktøjet, skal du aflevere det i et af varehusene, hvor personalet inspicerer værktøjet, læser din eventuelle kommentar og tjekker det ud af din bruger.</p>
                    <br> 
                    <p><span class="bold">Låner jeg et stykke værktøj?</span></p>
                    <p>Efter du har oprettet en bruger og købt et abonnement, skal du blot tage til et af Initechs fysiske varehuse, finde det værktøj du skal bruge, scanne det via checkin funktionen på hjemmesiden via din mobil og tage værktøjet med hjem.</p> 
                    <br> 
                    <p><span class="bold">Gør jeg x?</span></p>
                    <p>Se vores <a href="https://tectools.virtusb.com/bruger-manual">manual</a> for korte video instruktioner til brugen af hjemmesiden.</p>
                    <br> 
                    <p><span class="bold">Skal jeg gøre hvis jeg oplever en fejl på siden?</span></p>
                    <p>Hvis du oplever fejl på siden bedes du sende en besked med en god beskrivelse af fejlen via vores <a href="https://tectools.virtusb.com/contact">kontakt-side</a>.</p>
                </div>

                <h3 class="toggle solid-border section-title-large">Hvor...</h3>
                <div class="toggle-content" style="">
                    <p><span class="bold">Kan jeg låne værktøj?</span></p>
                    <p>Du kan låne værktøj i et af Initechs fysiske varehuse.</p>
                    <br>
                    <p><span class="bold">Kan jeg aflevere værktøj?</span></p>
                    <p>Du kan aflevere værktøj i et af Initechs fysiske varehuse.</p>
                    <br>
                    <p><span class="bold">Blev min reservation af?</span></p>
                    <p>En reservation udløber efter 24 eller 48 timer afhængig af abonnement, da værktøjet også skal være tilgængeligt for andre. Husk at låne det i denne tidsperiode. Du kan se dine reservationer på dit <a href="https://tectools.virtusb.com/dashboard#reservations-tab">dashboard</a>.</p>
                </div>

                <h3 class="toggle solid-border section-title-large">Hvad...</h3>
                <div class="toggle-content" style="">
                    <p><span class="bold">Gør kommentar-feltet?</span></p>
                    <p>Kommentar-feltet lader dig skrive en kort besked til Initechs personale. 
                    <p>Dette kunne fx være en kommentar til værktøjets tilstand ved checkin/checkud eller begrundelse for eventuelle skader.</p>
                    <p>Fx kan du skrive i dette felt, hvis en Trillebørs dæk er punkteret, en rundsavs klinge er slidt eller et par sko mangler såler.</p>
                    <p>Denne kommentar vil Initechs personale læse, og fx sende værktøjet til reparation. </p>
                </div>

                <h3 class="toggle solid-border section-title-large">Hvorfor...</h3>
                <div class="toggle-content" style="">
                    <p><span class="bold">Kan jeg ikke reservere flere stykker værktøj?</span></p>
                    <p>Dette kan være fordi dit abonnement ikke tillader flere, værktøjet allerede er reserveret eller at værktøjet ikke er på lager.</p>
                    <br>
                    <p><span class="bold">Fik jeg en bøde?</span></p>
                    <p>Du kan få en bøde, hvis du ikke afleverer til tiden eller hvis du har slemt misvedligeholdt et stykke lånt værktøj. Bøden afspejler hvor sent værktøjet blev afleveret eller graden af skaden på værktøjet.</p>
                    <br>
                    <p><span class="bold">Har I ikke værktøjet x og y?</span></p>
                    <p>Hvis du mener at vi mangler et stykke værktøj i vores kartotek, bedes du sende en besked med hvilket værktøj du ønsker tilføjet via vores <a href="https://tectools.virtusb.com/contact">kontakt-side</a>.</p>
                </div>

            </div>
        </div>
    </div>

</div>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/faq.css">

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/faq/faq.js"></script>




