<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

if (!isset($_GET['manufacturerid']) || !is_numeric($_GET['manufacturerid'])) {
    Helpers::outputError('Manufacturer ID mangler', 'h3', true);
    return;
}

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

$manufacturer = $TecTools->getManufacturer((int) $_GET['manufacturerid']);

?>

<div class="container mt4">
    <div class="row">
        <form method="post"  class="col s12 m6 offset-m3 tectool-form">
            <h1 class="center mb4 mt0">Rediger producent</h1>

            <div class="row mt2 mb0">
                <div class="input-field col s12">
                    <input value="<?= $manufacturer['ManufacturerName'] ?>" required id="manufacturer_name" name="manufacturer_name" type="text" class="validate">
                    <label for="manufacturer_name">Navn</label>
                </div>
            </div>

            <input type="hidden" name="post_endpoint" value="editManufacturer" />

            <input type="hidden" name="manufacturer_id" value="<?= $manufacturer['ManufacturerID'] ?>">

            <div class="row mb0">
                <div class="input-field col s12">
                    <input class="tec-submit-btn" type="submit" value="Gem">
                </div>
            </div>

            <div class="row mb0">
                <div class="input-field col s6 m0">
                    <button class="btn tec-btn" type="button" onclick="location.href = '/dashboard'">Tilbage</button>
                </div>
            </div>

        </form>
    </div>
</div>