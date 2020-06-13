<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

if (!isset($_GET['toolid']) || !is_numeric($_GET['toolid'])) {
    $this->RCMS->Functions->outputError('Tool ID mangler', 'h3', true);
    return;
}

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

$tool = $TecTools->getToolByID((int) $_GET['toolid']);

$listColumnCount = count($tool['Categories']) > 1 ? 2 : 1;

?>

<style>
    .image-container {
        padding: 10px;
        text-align: center;
    }

    .image-container img {
        max-width: 65%;
        object-fit: scale-down;
    }

    #tool-image-col {
        border: 2px solid #EEE;
        max-height: 400px;
        border-radius: 5px;
    }

    @media screen and (max-width: 992px) {
        h1 {
            margin-top: 0;
        }
    }

    #category-list {
        column-count: <?= $listColumnCount ?>;
        text-align: center;
        border-top: 1px solid #1d1d1d;
        margin-bottom: 2rem;
    }

    #category-list li {
        padding-top: 2rem;
    }
</style>

<div class="section no-pad-bot">
    <div class="container">
        <br><br>

        <div class="row">

            <div class="col s12 m12 l6">
                <h1 class="header center orange-text"><?= $tool['ManufacturerName'] . ' ' . $tool['ToolName'] ?></h1>

                <ul id="category-list">
                    <?php foreach ($tool['Categories'] as $category): ?>
                        <li><?= $category['CategoryName'] ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col s12 m12 l6">
                <div id="tool-image-col">
                    <div class="image-container">
                        <img src="<?= $TecTools->RELATIVE_TOOL_IMAGE_FOLDER . '/' . $tool['Image'] ?>" alt="">
                    </div>
                </div>

                <div style="margin-top: 2rem">
                    <p><strong>Lagerstatus: </strong><i class="fas fa-cubes cubes-icon <?= $tool['StatusID'] !== $TecTools::TOOL_AVAILABLE_STATUS ? 'not-available' : '' ?>"></i> <?= $tool['StatusName'] ?></p>

                    <?php if ($tool['EndDate'] !== null && $TecTools->RCMS->Functions::isFutureDateTimeString($tool['EndDate']) && ($tool['StatusID'] === $TecTools::TOOL_LOANED_OUT_STATUS || $tool['StatusID'] === $TecTools::TOOL_RESERVED_STATUS)): ?>
                        <p>Tilgængelig <span class="check-in-end-date" datetime="<?= $tool['EndDate'] ?>"></span></p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
        <br><br>
    </div>
</div>


<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/tools/tool.js"></script>

