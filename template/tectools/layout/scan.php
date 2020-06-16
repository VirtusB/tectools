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

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <button id="scan-btn" class="btn green-btn">Scan <i class="fal fa-scanner"></i></button>

        <div class="row center" style="margin-top: 4rem;">
            <div class="col s12 m8 l6 xl4 offset-m2 offset-l3 offset-xl4">
                <div id="barcode-scanner">

                </div>
            </div>
        </div>

        <h4 class="header center orange-text">Du skal tillade at siden må bruge kameraet</h4>
        <br><br>
    </div>
</div>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/scan.css">

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/libs/quagga.min.js"></script>
<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/scan/scan.js"></script>