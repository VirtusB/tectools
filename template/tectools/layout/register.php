<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

if ($this->RCMS->Login->isLoggedIn()) {
    header('Location: /dashboard');
}
?>

<div class="container mt0">
    <div class="row">
        <form method="post"  class="col s12 m4 offset-m4">
            <h1 class="center">Opret konto</h1>
            <div>
                <a style="text-align: center; display: block" href="/login">Har du allerede en konto? Log ind her</a>
            </div>

            <div style="text-align: center">
                <?php
                if (isset($_GET['emailtaken'])) {
                    Helpers::outputError('Bruger med den email eksisterer allerede', 'h6');
                }

                if (isset($_GET['confirm_password'])) {
                    Helpers::outputError('Adgangskoder er ikke ens', 'h6');
                }
                ?>
            </div>

            <div class="row mt2">
                <div class="input-field col s6">
                    <input autocomplete="off" required value="<?= $_SESSION['createUserPOST']['firstname'] ?? '' ?>" id="firstname" name="firstname" type="text" class="validate">
                    <label for="firstname">Fornavn</label>
                </div>

                <div class="input-field col s6">
                    <input autocomplete="off" required value="<?= $_SESSION['createUserPOST']['lastname'] ?? '' ?>" id="lastname" name="lastname" type="text" class="validate">
                    <label for="lastname">Efternavn</label>
                </div>

                <div class="input-field col s12">
                    <input autocomplete="off" required value="<?= $_SESSION['createUserPOST']['address'] ?? '' ?>" id="address" name="address" type="text" class="validate">
                    <label for="address">Adresse</label>
                </div>

                <div class="input-field col s6">
                    <input pattern="\d*" minlength="4" maxlength="4" autocomplete="off" required value="<?= $_SESSION['createUserPOST']['zipcode'] ?? '' ?>" id="zipcode" name="zipcode" type="text" class="validate">
                    <label for="zipcode">Postnr.</label>
                </div>

                <div class="input-field col s6">
                    <input autocomplete="off" required value="<?= $_SESSION['createUserPOST']['city'] ?? '' ?>" id="city" name="city" type="text" class="validate">
                    <label for="city">By</label>
                </div>

                <div class="input-field col s6">
                    <input autocomplete="off" id="email" name="email" type="email" required class="validate">
                    <label for="email">E-mail</label>
                </div>

                <div class="input-field col s6">
                    <input pattern="\d*" minlength="8" maxlength="8" autocomplete="off" required value="<?= $_SESSION['createUserPOST']['phone'] ?? '' ?>" id="phone" name="phone" type="text" class="validate">
                    <label for="phone">Tlf. nr.</label>
                </div>

                <div class="input-field col s6">
                    <input autocomplete="new-password" required id="password" name="password" type="password" class="validate">
                    <label for="password">Adgangskode</label>
                </div>

                <div class="input-field col s6">
                    <input autocomplete="new-password" required id="repeat_password" name="repeat_password" type="password" class="validate">
                    <label for="repeat_password">Gentag adgangskode</label>
                </div>

                <div class="col s12 center">
                    <label class="" for="consent">
                        <input onchange="document.getElementById('submitBtn').toggleAttribute('disabled');" required="required" class="" name="consent" placeholder="" id="consent" type="checkbox">
                        <span>Jeg har læst og accepteret <a target="_blank" href="/tos">betingelserne</a></span>
                    </label>
                </div>

            </div>

            <input name="create_new_user" type="hidden" value="1">

            <div class="row">
                <div class="input-field col s12">
                    <input id="submitBtn" disabled class="btn tec-submit-btn" type="submit" value="Opret konto">
                </div>
            </div>
        </form>
    </div>
</div>