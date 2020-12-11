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

<div id="store-locations-json" class="hide">
    <?= json_encode($TecTools->getStoreLocations()) ?>
</div>

<div class="section no-pad-bot">
    <?php if ($TecTools->RCMS->Login->isAdmin()): ?>
    <h4 class="center">Personale - Tjek Ud</h4>
    <p class="center mb0">Her kan du som Personale tjekke værktøj ud, så det kommer på lager igen</p>
    <hr style="width: 80%">
    <?php endif; ?>

    <div class="container" id="scan-container">
        <br><br>
        <button id="scan-btn" class="btn green-btn">Scan   <i class="fal fa-scanner"></i></button>

        <h6 class="center mt2">Scan med din smartphone <i class="fad fa-mobile-alt" style="font-size: 25px;vertical-align: middle;"></i></h6>

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
                <img style="max-width: 65%; object-fit: scale-down;" src="" alt="">
            </div>

            <div class="col s12 m8 l6 xl4 offset-m2 offset-l3 offset-xl4">
                <h4 style="word-break: break-word;" id="tool-name-manufacturer" class="header center orange-text"></h4>
            </div>


        </div>

        <br><br>
    </div>

    <div style="display: none" id="tool-container-admin">

        <div class="row center" style="">
            <div class="col s12 m8 l6 xl4 offset-m2 offset-l3 offset-xl4">
                <img style="max-width: 45%; object-fit: scale-down;" src="" alt="">
            </div>

            <div class="col s12 m8 l6 xl4 offset-m2 offset-l3 offset-xl4">
                <h4 style="word-break: break-word;" id="tool-name-manufacturer-admin" class="header center orange-text mt0"></h4>
            </div>


        </div>

        <div class="row center">
            <div class="col s12">
                <table class="responsive-table">
                    <thead>
                    <tr>
                        <th>Bruger ID</th>
                        <th>Udlejning start</th>
                        <th>Udlejning slut</th>
                        <th>Kommentar</th>
                    </tr>
                    </thead>

                    <tbody>
                    <tr>
                        <td id="user-id"></td>
                        <td id="start-date"></td>
                        <td id="end-date"></td>
                        <td id="comment"></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="col s10 offset-s1 mt2">
                <label for="tool-status">Status</label>
                <select id="tool-status" required class="mat-select">
                    <option selected value="1">På lager</option>
                    <option value="4">Ikke på lager</option>
                    <option value="5">Beskadiget</option>
                </select>

                <label style="display: flex; margin-top: 2rem;">
                    <input id="add-fine-checkbox" onchange="fineCheckBoxChange()" type="checkbox" />
                    <span>Tilføj bøde?</span>
                </label>
            </div>

            <div style="display: none" id="fine-container" class="col s10 offset-s1 mt2">
                <label for="fine-amount">Bøde størrelse (DKK)</label>
                <input min="2.5" max="999999" class="mb2" step="any" disabled value="2.5" type="number" id="fine-amount">

                <label for="fine-comment">Bøde kommentar</label>
                <textarea disabled id="fine-comment" class="materialize-textarea"></textarea>
            </div>

            <button onclick="checkOutTool(this.getAttribute('data-checkin-id'))" data-checkin-id="" id="check-out-btn" class="btn green-btn mt2">Tjek Ud   <i class="fad fa-dolly"></i></button>
        </div>

        <br><br>
    </div>
</div>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/scan.css">

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/libs/quagga.min.js"></script>
<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/scan/scan.js"></script>