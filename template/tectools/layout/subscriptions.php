<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];


$products = $this->RCMS->StripeWrapper->getStripeProducts();

if ($TecTools->RCMS->Login->isLoggedIn()) {
    Helpers::redirect('/my-subscription');
}

?>

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h2 class="header center">Abonnementer</h2>

        <div class="row center" style="margin-top: 4rem;">
            <?php foreach ($products as $key => $product): ?>
                <div class="col s12 m4 <?= $key % 2 === 0 ? 'offset-m1' : 'offset-m2' ?>">
                    <div class="card">

                        <div class="card-content center">
                            <h5 class=''><?= $product['name'] ?></h5>
                        </div>
                        <div class="card-content center">
                            <h2><small>kr. </small><?= $product['price'] ?>,-</h2>
                            <small class="grey-text">pr. md.</small>
                        </div>

                        <ul class='collection center'>
                            <?php foreach ($product['metadata'] as $prop): ?>
                                <li class='collection-item'>
                                    <?= $this->RCMS->StripeWrapper->isPremiumPlan($product, 200) ? "<strong>{$prop['value']} {$prop['description']}</strong>" : "{$prop['value']} {$prop['description']}" ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="card-content center">
                            <div class="row">
                                <div class="col s12">
                                    <a class="btn tec-btn" href="/register">Opret konto</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <div class="col s12 m4 offset-m1">
                <p class="grey-text">*Opsig fra dag til dag - ingen bindinger</p>
                <p class="grey-text">*Alle priser er inkl. moms</p>
            </div>
        </div>

        <br><br>
    </div>
</div>


<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/my-subscription.css">



