<?php

declare(strict_types=1);

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

/**
 * @var Template $this
 */

if (empty($_GET['hash'])) {
    Helpers::outputError('Ugyldigt bøde link', 'h3', true);
    return;
}

$paymentHash = $_GET['hash'];
$fine = $TecTools->CheckIns->getFineByHash($paymentHash);

if ($fine['FK_UserID'] !== $this->RCMS->Login->getUserID()) {
    Helpers::outputError('Du ejer ikke denne bøde', 'h3', true);
    return;
}

if ($fine['IsPaid'] === 1) {
    Helpers::outputError('Denne bøde er allerede betalt', 'h3', true);
    return;
}

?>

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h2 class="header center">Betal bøde</h2>

        <div class="row">
            <div class="col s12 m6 offset-m3" style="display: flex; justify-content: center">
                <div class="card-panel teal">
                    <ul class="">
                        <li class="valign-wrapper">
                            <span class="title">Bøde størrelse: DKK <?= $fine['FineAmount'] ?>,-</span>
                        </li>
                        <li class="valign-wrapper">
                            <span class="title">Bøde årsag: <?= $fine['FineComment'] ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col s12 center mt2">
                <label class="" for="consent">
                    <input onchange="document.getElementById('submitBtn').toggleAttribute('disabled');" required="required" class="" name="consent" placeholder="" id="consent" type="checkbox">
                    <span>Jeg har læst og accepteret <a target="_blank" href="/tos">betingelserne</a></span>
                </label>
            </div>

            <div class="col s12 m6 offset-m3 mt2">
                <form id="payment-form" action="" method="post">
                    <button style="width: 80% !important;" disabled id="submitBtn" type="submit" class="btn tec-submit-btn">Betal</button>
                    <p class="grey-text">* Opkrævningen hæves fra dit standard betalingskort</p>
                    <input type="hidden" name="post_endpoint" value="payFine">
                    <input type="hidden" name="payment_hash" value="<?= $_GET['hash'] ?>">
                </form>
            </div>

        </div>

        <br><br>
    </div>
</div>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/pay-fine.css">