<?php

declare(strict_types=1);

/**
 * @var $TecTools TecTools
 */
$TecTools = $GLOBALS['TecTools'];

$stores = $TecTools->getAllStores();

?>

<div class="container mb4">

    <h1 class="center">Vores butikker</h1>

    <?php foreach ($stores as $key => $store): ?>

        <?php $c = ($key + 1) % 2 === 0 ? 'even-row' : 'odd-row' ?>

        <div class="row store-row <?= $c ?>">
            <div class="col s12">
                <div class="content">
                    <div class="valign-wrapper">
                        <i class="fad fa-store"></i>

                        <p class="store-name">
                            <?= $store['StoreName'] ?>
                            <br>
                            <?= $store['Address']  ?>
                            <br>
                            <?= $store['ZipCode'] . ' ' . $store['City']  ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col s12">
                <div class="content">
                    <p><?= $store['StoreDescription'] ?></p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .store-row {
        background: #d8d8d8;
        padding-top: 20px;
        padding-bottom: 20px;
        border-radius: 8px;
    }

    /*.store-row {*/
    /*    background: #2C3840 !important;*/
    /*}*/

    .col.s12 {
        padding-left: 40px;
        padding-right: 40px;
    }

    .store-name {
        margin-left: 15px;
    }

    /*.odd-row .col.s12 {*/
    /*    padding-left: 40px;*/
    /*}*/

    /*.even-row .col.s12 {*/
    /*    padding-right: 40px;*/
    /*}*/

    /*.even-row .content {*/
    /*    float: right;*/
    /*}*/

    .store-row i {
        font-size: 50px;
    }

    .store-row h5 {
        margin: 0;
        margin-top: 20px;
    }

    /*@media all and (max-width: 600px) {*/
    /*    .store-row {*/
    /*        padding-top: 15px;*/
    /*        padding-bottom: 15px;*/
    /*        border-radius: 8px;*/
    /*    }*/

    /*    .store-row i {*/
    /*        font-size: 30px;*/
    /*    }*/

    /*    .store-row h5 {*/
    /*        font-size: 1.4rem;*/
    /*    }*/
    /*}*/
</style>