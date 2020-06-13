<?php

declare(strict_types=1);

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

/**
 * @var GlobalHandlers $GlobalHandlers
 */
$GlobalHandlers = $GLOBALS['GlobalHandlers'];

$tools = $TecTools->getAllTools();


?>

<div class="container">
    <form action="" method="get">
        <div class="row" style="margin-top: 2rem;">
            <div class="col s12 m12 l4 xl4">
                <input name="search-text" type="text" placeholder="Fritekst...">
            </div>

            <div id="category-select-col" class="col s12 m12 l4 xl4">
                <select multiple name="categories[]" id="category-select">
                    <option selected disabled value="SORT_CATEGORIES">Vælg kategori</option>
                    <option value="1">Test</option>
                    <option value="2">Test</option>
                </select>
            </div>

            <div style="justify-content: flex-end" class="col s12 m12 l4 xl4 valign-wrapper">
                <button style="width: 50%;" id="filter-tools-btn" type="submit" class="btn green">Søg</button>
            </div>
        </div>
        <div class="row" id="tools-row">

            <?php foreach ($tools as $tool): ?>

                <div class="col s12 m6 l3 xl3">
                    <div onclick="location.href = '/tool?toolid=<?= $tool['ToolID'] ?>'" class="card">
                        <div class="card-image">
                            <img src="<?= $TecTools->RELATIVE_TOOL_IMAGE_FOLDER . '/' . $tool['Image'] ?>" alt="">
                        </div>
                        <div class="card-content">
                            <span class="card-title black-text"><?= $tool['ToolName'] ?></span>
                            <p><?= $tool['ManufacturerName'] ?></p>
                            <br>
                            <p>
                                <i class="fas fa-cubes cubes-icon <?= $tool['StatusID'] !== $TecTools::TOOL_AVAILABLE_STATUS ? 'not-available' : '' ?>"></i> <?= $tool['StatusName'] ?>
                            </p>

                            <br>
                        </div>
                        <div class="card-action">
                            <ul>
                                <?php foreach ($tool['Categories'] as $category): ?>
                                    <li><?= $category['CategoryName'] ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>

    </form>
</div>
</div>


<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/frontpage.css">

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/frontpage/frontpage.js"></script>


