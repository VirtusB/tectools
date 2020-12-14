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
        <form onsubmit="validateRegister(event)" method="post"  class="col s12 m4 offset-m4">
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
                    <span class="helper-text" data-error="Må ikke være tom"></span>
                </div>

                <div class="input-field col s6">
                    <input autocomplete="off" required value="<?= $_SESSION['createUserPOST']['lastname'] ?? '' ?>" id="lastname" name="lastname" type="text" class="validate">
                    <label for="lastname">Efternavn</label>
                    <span class="helper-text" data-error="Må ikke være tom"></span>
                </div>

                <div class="input-field col s12">
                    <input autocomplete="off" required value="<?= $_SESSION['createUserPOST']['address'] ?? '' ?>" id="address" name="address" type="text" class="validate">
                    <label for="address">Adresse</label>
                    <span class="helper-text" data-error="Må ikke være tom"></span>
                </div>

                <div class="input-field col s6">
                    <input pattern="\d*" minlength="4" maxlength="4" autocomplete="off" required value="<?= $_SESSION['createUserPOST']['zipcode'] ?? '' ?>" id="zipcode" name="zipcode" type="text" class="validate">
                    <label for="zipcode">Postnr.</label>
                    <span class="helper-text" data-error="Skal være 4 tal"></span>
                </div>

                <div class="input-field col s6">
                    <input autocomplete="off" required value="<?= $_SESSION['createUserPOST']['city'] ?? '' ?>" id="city" name="city" type="text" class="validate">
                    <label for="city">By</label>
                    <span class="helper-text" data-error="Må ikke være tom"></span>
                </div>

                <div class="input-field col s6">
                    <input autocomplete="off" id="email" name="email" type="email" required class="validate">
                    <label for="email">E-mail</label>
                    <span class="helper-text" data-error="Ugyldig e-mail"></span>
                </div>

                <div class="input-field col s6">
                    <input pattern="\d*" minlength="8" maxlength="8" autocomplete="off" required value="<?= $_SESSION['createUserPOST']['phone'] ?? '' ?>" id="phone" name="phone" type="text" class="validate">
                    <label for="phone">Tlf. nr.</label>
                    <span class="helper-text" data-error="Skal være 8 tal"></span>
                </div>

                <div class="input-field col s6">
                    <input minlength="8" autocomplete="new-password" required id="password" name="password" type="password" class="validate">
                    <label for="password">Adgangskode</label>
                    <span class="helper-text" data-error="Mindst 8 karakterer"></span>
                </div>

                <div class="input-field col s6">
                    <input  minlength="8" autocomplete="new-password" required id="repeat_password" name="repeat_password" type="password" class="validate">
                    <label for="repeat_password">Gentag adgangskode</label>
                    <span class="helper-text" data-error="Mindst 8 karakterer"></span>
                </div>

                <div class="col s12 center">
                    <label class="" for="consent">
                        <input onchange="toggleAttr('#submitBtn', 'disabled', 'disabled')" required="required" class="validate" name="consent" placeholder="" id="consent" type="checkbox">
                        <span>Jeg har læst og accepteret <a target="_blank" href="/tos">betingelserne</a></span>
                    </label>
                    <span class="helper-text" data-error="Skal tjekkes"></span>
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

