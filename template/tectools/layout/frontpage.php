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

$categories = $TecTools->Categories->getAllCategories();
$tools = $TecTools->getAllToolsWithFilters();
$checkInsMarquee = $TecTools->getCheckInsForMarquee();
$chunks = array_chunk($checkInsMarquee, 3);

?>

<style>
    .hero-container:before {
        background-image: url(<?= $this->RCMS->getTemplateFolder() ?>/images/top_pic_hero.jpg);
    }
</style>

<div class="container">
    <div class="hero-container">
        <span>Nem og billig adgang til alle slags værktøj</span>
        <span>Planer starter fra kun 200 kroner / md.</span>
        <span>100% CO2 neutral</span>
    </div>

    <form action="" method="get">
        <div class="row" style="margin-top: 2rem; margin-bottom: 0">
            <div class="col s12 m12 l4 xl4">
                <input value="<?= isset($_GET['search-text']) ? $_GET['search-text'] : '' ?>" name="search-text" type="text" placeholder="Fritekst...">
            </div>

            <div id="category-select-col" class="col s12 m12 l4 xl4">
                <select multiple name="categories[]" id="category-select">
                    <option disabled="disabled" value="">Vælg kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option <?= isset($_GET['categories']) && in_array($category['CategoryID'], $_GET['categories'], false) ? 'selected' : '' ?> value="<?= $category['CategoryID'] ?>"><?= $category['CategoryName'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="justify-content: flex-end" class="col s12 m12 l4 xl4 valign-wrapper">
                <div id="only_in_stock_container">
                    <input type="hidden" name="only_in_stock" value="0" />
                    <input value="1" <?= isset($_GET['only_in_stock']) ? ($_GET['only_in_stock'] == '1' ? 'checked' : '') : 'checked' ?> id="only_in_stock" name="only_in_stock" type="checkbox">
                    <label for="only_in_stock">Kun på lager</label>
                </div>

                <button style="width: 50%;" id="filter-tools-btn" type="submit" class="btn green-btn">Søg</button>
            </div>

        </div>
        <div class="row" id="tools-row">

            <div class="col s12">
                <?php
                $TecTools->displayToolCountMessage();
                ?>
            </div>

            <?php foreach ($tools as $tool): ?>

                <div class="col s12 m6 l3 xl3">
                    <div onclick="location.href = '/tools/view?toolid=<?= $tool['ToolID'] ?>'" class="card large">
                        <div class="card-image">
                            <img class="" src="<?= $TecTools->RELATIVE_TOOL_IMAGE_FOLDER . '/' . $tool['Image'] ?>" alt="">
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

            <?php if (count($tools)): ?>

            <div class="col s12">
                <div class="pagination-container">
                    Side

                    <?php
                        $TecTools->displayFrontPagePagination();
                    ?>
                </div>
            </div>

            <?php else: ?>

            <div class="col s12">
                <h5>Der er ingen værktøj, der matcher dit filter</h5>
            </div>

            <?php endif; ?>

    </form>

    <div id="latest-checkins" class="row">
        <div class="col s12 mt2 marquee-container">

            <h5 class="center mb2">Seneste lån foretaget af vores medlemmer!</h5>

            <?php foreach ($chunks as $k => $checkIns): ?>
            <div class="marquee" style="<?= $k !== 0 ? 'display: none;' : '' ?>">
                <div class="marquee-inner">

                    <?php foreach ($checkIns as $key => $checkIn): ?>
                        <div class="marquee-element-container">
                            <div onclick="location.href = '/tools/view?toolid=<?= $checkIn['FK_ToolID'] ?>'" class="marquee-element">
                                <div class="marquee-img-container">
                                    <img class="marquee-img" src="<?= $checkIn['Image'] ?>" alt="">
                                </div>

                                <div class="marquee-content">
                                    <p><?= $checkIn['FirstName'] ?> lejede:<br><?= $checkIn['ManufacturerName'] ?> <?= $checkIn['ToolName'] ?><br><span class="render-datetime" datetime="<?= $checkIn['StartDate'] ?>"></span></p>
                                </div>
                            </div>
                        </div>

                        <?php if (count($checkIns) !== ($key + 1)): ?>
                            <div class="marquee-element-divider-container">
                                <div class="marquee-element-divider">
                                    <i class="fa fa-horizontal-rule"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach;?>

        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/frontpage.css">

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/frontpage/frontpage.js"></script>



