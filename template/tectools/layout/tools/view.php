<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

if (!isset($_GET['toolid']) || !is_numeric($_GET['toolid'])) {
    Helpers::outputError('Tool ID mangler', 'h3', true);
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
    #category-list {
        column-count: <?= $listColumnCount ?>;
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

                <?php if ($this->RCMS->Login->getSubName()): ?>
                <div>
                    <?php if ($tool['StatusID'] === $TecTools::TOOL_AVAILABLE_STATUS && !$TecTools->hasUserReachedMaxReservations()): ?>
                        <form method="post">
                            <input type="hidden" name="post_endpoint" value="addReservation">
                            <input type="hidden" name="tool_id" value="<?= $tool['ToolID'] ?>">
                            <button class="btn tec-btn mt2" type="submit">Reserver <i class="fal fa-cart-arrow-down right"></i></button>
                        </form>
                    <?php else: ?>
                        <form method="post">
                            <button disabled class="btn tec-btn mt2" type="submit">Reserver <i class="fal fa-cart-arrow-down right"></i></button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div style="margin-top: 2rem">
                    <p><strong>Lagerstatus: </strong><i class="fas fa-cubes cubes-icon <?= $tool['StatusID'] !== $TecTools::TOOL_AVAILABLE_STATUS ? 'not-available' : '' ?>"></i> <?= $tool['StatusName'] ?></p>

                    <?php if ($tool['CheckedOut'] !== null && $tool['CheckedOut'] === 0 && $tool['EndDate'] !== null && Helpers::isFutureDateTimeString($tool['EndDate']) && ($tool['StatusID'] === $TecTools::TOOL_LOANED_OUT_STATUS || $tool['StatusID'] === $TecTools::TOOL_RESERVED_STATUS)): ?>
                        <p>Forventes på lager <span class="check-in-end-date" datetime="<?= $tool['EndDate'] ?>"></span></p>
                    <?php endif; ?>

                    <br>
                    <h6 class="grey-text">Beskrivelse</h6>

                    <?= $tool['Description'] ?>
                </div>
            </div>

        </div>
        <br><br>
    </div>
</div>

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/tools/tool.js"></script>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/tools-view.css">
