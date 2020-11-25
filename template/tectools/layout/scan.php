<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

?>

<div id="store-locations-json" class="hiddendiv">
    <?= json_encode($TecTools->getStoreLocations()) ?>
</div>

<div class="section no-pad-bot">
    <div class="container" id="scan-container">
        <br><br>
        <button id="scan-btn" class="btn green-btn">Scan   <i class="fal fa-scanner"></i></button>

        <div class="row center" style="margin-top: 4rem;">
            <div class="col s12 m8 l6 xl4 offset-m2 offset-l3 offset-xl4">
                <div id="barcode-scanner">

                </div>
            </div>
        </div>

        <p class="center orange-text info-paragraph">
            <i class="fas fa-info" style="float: left; color: #2196F3;"></i>
            Du skal tillade at siden må bruge kameraet
        </p>

        <br><br>
    </div>

    <div style="display: none;" class="container" id="tool-container">
        <br><br>

        <button onclick="startGeofence(this.getAttribute('data-barcode'))" data-barcode="" id="check-in-btn" class="btn green-btn">Lån   <i class="fal fa-shopping-basket"></i></button>

        <div style="margin-top: 2rem;" class="row center">
            <div class="col s12">
                <h6 id="error-msg" class="red-text"></h6>
            </div>
        </div>

        <div class="row center" style="margin-top: 4rem;">
            <div class="col s12 m8 l6 xl4 offset-m2 offset-l3 offset-xl4">
                <img style="max-width: 65%; object-fit: scale-down;" id="tool-name" src="" alt="">
            </div>

            <div class="col s12 m8 l6 xl4 offset-m2 offset-l3 offset-xl4">
                <h4 style="word-break: break-word;" id="tool-name-manufacturer" class="header center orange-text"></h4>
            </div>


        </div>

        <br><br>
    </div>
</div>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/scan.css">

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/libs/quagga.min.js"></script>
<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/scan/scan.js"></script>