<?php

/**
 * @var $TecTools TecTools
 */
$TecTools = $GLOBALS['TecTools'];

?>

<style>
    #add_tool_form input, #add_tool_form select {
        margin-bottom: 2rem;
    }
</style>

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h1 class="header center orange-text">Opret værktøj</h1>

        <div class="row center">
            <div class="col s12 m6 l6 xl6 offset-m3 offset-l3 offset-xl3">
                <?php
                if (isset($_SESSION['create_tool_image_error'])) {
                    $this->RCMS->Functions->outputError($_SESSION['create_tool_image_error'], 'h5', true);
                    unset($_SESSION['create_tool_image_error']);
                }
                ?>
                <form enctype="multipart/form-data" id="add_tool_form" action="" method="POST">

                    <label>Navn</label>
                    <input id="tool_name" required name="tool_name" type="text" placeholder="Navn på værktøj">

                    <label>Beskrivelse</label>
                    <input required name="description" type="text" placeholder="Kort beskrivelse">

                    <label>Status</label>
                    <select required class="browser-default" name="status">
                        <option value="" disabled selected>Vælg status</option>
                        <?php foreach ($TecTools->getStatusList() as $status): ?>
                            <option value="<?= $status['id'] ?>"><?= $status['name'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Producent</label>
                    <select id="manufacturer_id" required class="browser-default" name="manufacturer_id">
                        <option value="" disabled selected>Vælg producent</option>
                        <?php foreach ($TecTools->getAllManufacturers() as $manufacturer): ?>
                            <option value="<?= $manufacturer['ManufacturerID'] ?>"><?= $manufacturer['ManufacturerName'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label>Stregkode</label>
                    <input onkeydown="return false;" style="caret-color: transparent !important; pointer-events: none" id="barcode" type="text" required name="barcode" placeholder="Stregkode">

                    <div class="file-field input-field">
                        <div class="btn">
                            <span>Billede</span>
                            <input required name="image" type="file">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text">
                        </div>
                    </div>

                    <input type="hidden" name="add_tool" value="1" />

                    <br><br>
                    <button id="add_tool_btn" class="btn" type="submit">Opret værktøj</button>

                </form>
            </div>
        </div>
        <br><br>
    </div>
</div>

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/tools/create-tool.js"></script>

