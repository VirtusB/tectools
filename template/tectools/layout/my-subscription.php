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

$userProduct = null;

if ($this->RCMS->Login->getStripeID()) {
    $productID = $this->RCMS->StripeWrapper->getProductIDForCustomer($this->RCMS->Login->getStripeID());
    if ($productID) {
        $userProduct = $this->RCMS->StripeWrapper->getStripeProduct($productID);
    }
}
?>



<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h1 class="header center orange-text">Abonnement</h1>

        <div class="row center" style="margin-top: 4rem;">
            <?php foreach ($products as $key => $product): ?>
            <div class="col s12 m4 <?= $key % 2 === 0 ? 'offset-m1' : 'offset-m2' ?>">
                <div class="card">

                    <div class="card-content center">
                        <h5 class=''><?= $product['name'] ?></h5>
                    </div>
                    <div class="card-content center">
                        <h2 class="<?= $this->RCMS->StripeWrapper->isPremiumPlan($product) ? 'purple-text' : 'red-text' ?>"><small>kr.</small><?= $product['price'] ?>,-</h2>
                        <small class="grey-text">pr. md.</small>
                    </div>

                    <ul class='collection center'>
                        <?php foreach ($product['metadata'] as $prop): ?>
                        <li class='collection-item'>
                            <strong><?= $prop['value'] ?></strong> <?= $prop['description'] ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="card-content center">
                        <div class="row">
                            <div class="col s12">
                                    <?php
                                    if ($userProduct) {
                                        if ($userProduct['id'] === $product['id']) {
                                            include __DIR__ . '/partials/cancel-subscription-form.php';
                                        } else if ($product['price'] > $userProduct['price']) {
                                            include __DIR__ . '/partials/upgrade-subscription-form.php';
                                        } else {
                                            include __DIR__ . '/partials/downgrade-subscription-form.php';
                                        }
                                    } else {
                                        include __DIR__ . '/partials/new-subscription-form.php';
                                    }
                                    ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <br><br>
    </div>
</div>


<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/my-subscription.css">



