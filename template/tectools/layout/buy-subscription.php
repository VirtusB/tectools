<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

$productID = $_GET['product_id'];

$product = $this->RCMS->StripeWrapper->getStripeProduct($productID);

?>

<script src="https://js.stripe.com/v3/"></script>

<input type="hidden" id="billing-name" value="<?= $this->RCMS->Login->getFirstName() . ' ' . $TecTools->RCMS->Login->getLastName() ?>">

<style>
    span.title {
        margin-left: 10px;
    }

    @media screen and (max-width: 380px) {
        h2 {
            font-size: 3rem;
        }
    }
</style>

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h2 class="header center"><?= $product['name'] ?>-abonnementet</h2>

        <h5 class="center">I dette abonnement får du følgende services:</h5>

        <div class="row">
            <div class="col s12 m6 offset-m3">
                <ul class="">
                    <?php foreach ($product['metadata'] as $prop): ?>
                        <li>
                            <div style="display: flex; justify-content: center" class="valign-wrapper">
                                <i class="material-icons">grade</i>
                                <span class="title"><?= $prop['value'] . ' ' . $prop['description'] ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="row">
            <div class="col s10 offset-s1 m8 offset-m2 l4 l6 offset-l3 xl4 offset-xl4">
                <div id="card-element" class="MyCardElement">
                    <!-- Elements will create input elements here -->
                </div>

                <div id="card-errors" role="alert"></div>
            </div>

            <div class="col s12 center mt2">
                <label class="" for="consent">
                    <input onchange="document.getElementById('submitBtn').toggleAttribute('disabled');" required="required" class="" name="consent" placeholder="" id="consent" type="checkbox">
                    <span>Jeg har læst og accepteret <a target="_blank" href="/tos">betingelserne</a></span>
                </label>
            </div>

            <div class="col s12 m6 offset-m3 mt2">
                <form id="payment-form" action="" method="post">
                    <button disabled id="submitBtn" type="submit" class="btn tec-submit-btn">Abonner</button>

                    <input type="hidden" id="product_id" name="product_id" value="<?= $_GET['product_id'] ?>">

                    <input type="hidden" id="price_id" name="price_id" value="<?= $_GET['price_id'] ?>">

                    <input type="hidden" id="customer_id" name="customer_id" value="<?= $_GET['customer_id'] ?>">
                </form>
            </div>

        </div>

        <br><br>
    </div>
</div>

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/buy-subscription/buy-subscription.js"></script>

