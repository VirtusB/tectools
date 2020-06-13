<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

if (!isset($_GET['manufacturerid']) || !is_numeric($_GET['manufacturerid'])) {
    $this->RCMS->Functions->outputError('Manufacturer ID mangler', 'h3', true);
    return;
}

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

$manufacturer = $TecTools->getManufacturer((int) $_GET['manufacturerid']);

?>

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h1 class="header center orange-text">Rediger producent</h1>

        <div class="row center">
            <div class="col s12 m6 l6 xl6 offset-m3 offset-l3 offset-xl3">
                <form class="tectool-form" id="edit_manufacturer_form" action="" method="POST">

                    <label>Navn</label>
                    <input value="<?= $manufacturer['ManufacturerName'] ?>" id="manufacturer_name" required name="manufacturer_name" type="text" placeholder="Navn på producent">


                    <input type="hidden" name="edit_manufacturer" value="1" />

                    <input type="hidden" name="manufacturer_id" value="<?= $manufacturer['ManufacturerID'] ?>">

                    <br><br>
                    <button id="edit_manufacturer_btn" class="btn" type="submit">Gem</button>
                    <button class="btn" type="button" onclick="history.back()">Tilbage</button>

                </form>
            </div>
        </div>
        <br><br>
    </div>
</div>


