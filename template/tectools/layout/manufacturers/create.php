<?php

declare(strict_types=1);

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

?>

<div class="container mt4">
    <div class="row">
        <form method="post"  class="col s12 m6 offset-m3 tectool-form">
            <h1 class="center mb4 mt0">Opret producent</h1>

            <div class="row mt2 mb0">
                <div class="input-field col s12">
                    <input required id="manufacturer_name" name="manufacturer_name" type="text" class="validate">
                    <label for="manufacturer_name">Navn</label>
                </div>
            </div>

            <input type="hidden" name="post_endpoint" value="addManufacturer" />

            <div class="row mb0">
                <div class="input-field col s12">
                    <input class="tec-submit-btn" type="submit" value="Opret producent">
                </div>
            </div>

            <div class="row mb0">
                <div class="input-field col s6 m0">
                    <button class="btn tec-btn" type="button" onclick="history.back()">Tilbage</button>
                </div>
            </div>

        </form>
    </div>
</div>
