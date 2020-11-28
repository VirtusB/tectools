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

$userProduct = $TecTools->Users->getUserProduct($this->RCMS->Login->getUserID());

?>

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h2 class="header center">Dit abonnement</h2>

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
                                    <?php if ($userProduct): ?>
                                        <?php if ($userProduct['id'] === $product['id']): ?>
                                            <form onsubmit="confirm('Er du helt sikker?') === false && event.preventDefault()" action="" method="post">
                                                <input type="hidden" name="cancel_subscription" value="1">

                                                <input type="hidden" name="post_endpoint" value="cancelSubscription">

                                                <button class='btn red cancel-subscription'>Opsig</button>
                                            </form>
                                        <?php elseif ($product['price'] > $userProduct['price']): ?>
                                            <form action="" method="post">
                                                <input type="hidden" name="upgrade_subscription" value="1">

                                                <input type="hidden" name="post_endpoint" value="upgradeDowngradeSubscription">

                                                <input type="hidden" name="price_id" value="<?= $this->RCMS->StripeWrapper->getPlan($product['id'])->id ?>">

                                                <input type="hidden" name="product_name" value="<?= $product['name'] ?>">

                                                <button class='btn green upgrade-subscription'>Opgrader</button>
                                            </form>
                                        <?php else: ?>
                                            <form action="" method="post">
                                                <input type="hidden" name="downgrade_subscription" value="1">

                                                <input type="hidden" name="post_endpoint" value="upgradeDowngradeSubscription">

                                                <input type="hidden" name="price_id" value="<?= $this->RCMS->StripeWrapper->getPlan($product['id'])->id ?>">

                                                <input type="hidden" name="product_name" value="<?= $product['name'] ?>">

                                                <button class='btn orange downgrade-subscription'>Nedgrader
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <form class="newSubscriptionForm" action="/buy-subscription" method="get">
                                            <input type="hidden" name="post_endpoint" value="newSubscription">

                                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                                            <input type="hidden" name="product_name" value="<?= $product['name'] ?>">

                                            <input type="hidden" name="price_id" value="<?= $this->RCMS->StripeWrapper->getPlan($product['id'])->id ?>">

                                            <input type="hidden" name="customer_id" value="<?= $TecTools->Users->getStripeID() ?>">

                                            <button type="submit" class='btn green new-subscription'>Vælg</button>
                                        </form>
                                    <?php endif; ?>
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
            </div>
        </div>

        <br><br>
    </div>
</div>


<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/my-subscription.css">






