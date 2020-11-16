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

$userProduct = $TecTools->getUserProduct($this->RCMS->Login->getUserID());

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
                            <?= $this->RCMS->StripeWrapper->isPremiumPlan($product) ? "<strong>{$prop['value']} {$prop['description']}</strong>" : "{$prop['value']} {$prop['description']}" ?>
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

        <div class="row">
            <div class="col s12 m4 offset-m1">
                <p class="grey-text">*Opsig fra dag til dag - ingen bindinger</p>
            </div>
        </div>

        <br><br>
    </div>
</div>


<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/my-subscription.css">

<script src="https://js.stripe.com/v3/"></script>

<script>
    var stripe = Stripe('pk_test_51GxgFXDehEC5bZAdqTODhHU0ptCC2ejKNBhnZUcLQHjr1tsIZpGJGRv7FuZgMla5JYP747dbNNAXg0yRlUH0HP1R00DvkeG6wM'); // test eller production??

    var newSubscriptionBtn = document.getElementById('new-subscription');

    newSubscriptionBtn.addEventListener('click', function (e) {
        e.preventDefault();

        var formData = new FormData();
        formData.append('new_subscription', '1');

        fetch(location.origin + location.pathname, {
            method: 'POST',
            data: formData

        })
            .then(function (response) {
                return response.json();
            })
            .then(function (session) {
                return stripe.redirectToCheckout({sessionId: session.id});
            })
            .then(function (result) {
                // If `redirectToCheckout` fails due to a browser or network
                // error, you should display the localized error message to your
                // customer using `error.message`.
                if (result.error) {
                    alert(result.error.message);
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
            });
    });
</script>


<!-- Modal Structure -->
<div id="payment-modal" class="modal">
    <div class="modal-content">

    </div>

    <div class="modal-footer">
        <a href="#!" class="modal-close waves-effect waves-green btn-flat">Annuller</a>
    </div>
</div>



