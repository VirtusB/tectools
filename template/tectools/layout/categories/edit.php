<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

if (!isset($_GET['categoryid']) || !is_numeric($_GET['categoryid'])) {
    Functions::outputError('Category ID mangler', 'h3', true);
    return;
}

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

$category = $TecTools->getCategory((int) $_GET['categoryid']);

?>

<div class="container mt4">
    <div class="row">
        <form method="post"  class="col s12 m6 offset-m3 tectool-form">
            <h1 class="center mb4 mt0">Rediger kategori</h1>

            <div class="row mt2 mb0">
                <div class="input-field col s12">
                    <input value="<?= $category['CategoryName'] ?>" required id="category_name" name="category_name" type="text" class="validate">
                    <label for="category_name">Navn</label>
                </div>
            </div>

            <input type="hidden" name="post_endpoint" value="editCategory" />
            <input type="hidden" name="category_id" value="<?= $category['CategoryID'] ?>">

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
