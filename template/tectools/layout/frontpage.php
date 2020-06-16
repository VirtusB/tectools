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

$categories = $TecTools->getAllCategories();

if (isset($_GET['search-text']) || isset($_GET['categories'])) {
    $tools = $TecTools->getAllToolsWithFilters($_GET);
} else {
    $tools = $TecTools->getAllTools();
}

?>

<div class="container">
    <form action="" method="get">
        <div class="row" style="margin-top: 2rem;">
            <div class="col s12 m12 l4 xl4">
                <input value="<?= isset($_GET['search-text']) ? $_GET['search-text'] : '' ?>" name="search-text" type="text" placeholder="Fritekst...">
            </div>

            <div id="category-select-col" class="col s12 m12 l4 xl4">
                <select multiple name="categories[]" id="category-select">
                    <option <?= isset($_GET['categories']) ? '' : 'selected' ?> disabled value="SORT_CATEGORIES">Vælg kategori</option>
                    <?php foreach ($categories as $category): ?>
                        <option <?= isset($_GET['categories']) && in_array($category['CategoryID'], $_GET['categories'], false) ? 'selected' : '' ?> value="<?= $category['CategoryID'] ?>"><?= $category['CategoryName'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="justify-content: flex-end" class="col s12 m12 l4 xl4 valign-wrapper">
                <button style="width: 50%;" id="filter-tools-btn" type="submit" class="btn green-btn">Søg</button>
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


