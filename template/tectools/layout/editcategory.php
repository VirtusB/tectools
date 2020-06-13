<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

if (!isset($_GET['categoryid']) || !is_numeric($_GET['categoryid'])) {
    $this->RCMS->Functions->outputError('Category ID mangler', 'h3', true);
    return;
}

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

$category = $TecTools->getCategory((int) $_GET['categoryid']);

?>


<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h1 class="header center orange-text">Rediger kategori</h1>

        <div class="row center">
            <div class="col s12 m6 l6 xl6 offset-m3 offset-l3 offset-xl3">
                <form class="tectool-form" id="edit_category_form" action="" method="POST">

                    <label>Navn</label>
                    <input value="<?= $category['CategoryName'] ?>" id="category_name" required name="category_name" type="text" placeholder="Navn på kategori">


                    <input type="hidden" name="edit_category" value="1" />

                    <input type="hidden" name="category_id" value="<?= $category['CategoryID'] ?>">

                    <br><br>
                    <button id="edit_category_btn" class="btn" type="submit">Gem</button>
                    <button class="btn" type="button" onclick="history.back()">Tilbage</button>

                </form>
            </div>
        </div>
        <br><br>
    </div>
</div>


