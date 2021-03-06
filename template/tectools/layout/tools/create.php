<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

$categories = $TecTools->Categories->getAllCategories();

?>

<!-- Main Quill library TODO: skal loades lokalt -->
<script src="//cdn.quilljs.com/1.3.6/quill.min.js"></script>

<!-- Theme included stylesheets -->
<link href="//cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link href="//cdn.quilljs.com/1.3.6/quill.bubble.css" rel="stylesheet">

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h1 class="header center mt0">Opret værktøj</h1>

        <div class="row center">
            <div class="col s12 m6 l6 xl6 offset-m3 offset-l3 offset-xl3">
                <?php
                if (isset($_SESSION['tool_image_upload_error'])) {
                    Helpers::outputError($_SESSION['tool_image_upload_error'], 'h5', true);
                    unset($_SESSION['tool_image_upload_error']);
                }
                ?>
                <form class="tectool-form" enctype="multipart/form-data" id="add_tool_form" action="" method="POST">

                    <label>Navn</label>
                    <input id="tool_name" required name="tool_name" type="text" placeholder="Navn på værktøj">

                    <label for="des-editor">Beskrivelse</label>
                    <div id="des-editor">

                    </div>

                    <textarea style="display: none" name="description" id="description" cols="30" rows="10">

                    </textarea>

                    <br>

                    <label for="status">Status</label>
                    <select id="status" required="required" class="mat-select validate" name="status">
                        <option value="" disabled selected>Vælg status</option>
                        <?php foreach ($TecTools->getAllStatuses() as $status): ?>
                            <option value="<?= $status['StatusID'] ?>"><?= $status['StatusName'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="manufacturer_id">Producent</label>
                    <select id="manufacturer_id" required="required" class="mat-select validate" name="manufacturer_id">
                        <option value="" disabled selected>Vælg producent</option>
                        <?php foreach ($TecTools->Manufacturers->getAllManufacturers() as $manufacturer): ?>
                            <option value="<?= $manufacturer['ManufacturerID'] ?>"><?= $manufacturer['ManufacturerName'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="categories">Kategorier</label>
                    <select id="categories" style="min-height: 100px;" multiple required="required" class="mat-select validate" name="categories[]">
                        <option disabled="disabled" value="">Vælg kategorier</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['CategoryID'] ?>"><?= $category['CategoryName'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div class="file-field input-field">
                        <div class="btn">
                            <span>Billede</span>
                            <input accept="image/png, image/jpeg, image/jpg" onchange="updateImagePreview(this, 'tool-image');" required name="image" type="file">
                        </div>
                        <div class="file-path-wrapper">
                            <input class="file-path validate" type="text">
                        </div>
                    </div>

                    <img id="tool-image" style="max-height: 200px;" src="" alt="">

                    <input type="hidden" name="post_endpoint" value="addTool" />

                    <div class="row mb0">
                        <div class="input-field col s12 ">
                            <input class="tec-submit-btn" type="submit" value="Opret værktøj">
                        </div>
                    </div>

                    <div class="row mb0" style="text-align: left">
                        <div class="input-field col s6 m0">
                            <button class="btn tec-btn" type="button" onclick="location.href = '/dashboard'">Tilbage</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
        <br><br>
    </div>
</div>


<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/tools/edit-create-tool.js"></script>
