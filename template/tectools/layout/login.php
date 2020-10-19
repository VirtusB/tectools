<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

if ($this->RCMS->Login->isLoggedIn()) {
    header('Location: /dashboard');
}
?>

<div class="container mt4">
    <div class="row">

        <?php
        if (isset($_GET['wrong_email_or_password'])) {
            $this->RCMS->Functions->outputError('Forkert email eller adgangskode', 'h6');
        }

        if (isset($_GET['userInfoChanged'])) {
            echo '<h6>Da du har ændret dine brugerinformationer, skal du logge ind igen</h6>';
        }
        ?>

        <form method="post" class="col s12 m4 offset-m4">
            <h1 class="center">Log ind</h1>
            <div>
                <a style="text-align: center; display: block" href="/register">Ingen konto? Opret her</a>
            </div>

            <div class="row mt2">
                <div class="input-field col s12">
                    <input autocomplete="off" required id="username" name="email" type="email" class="validate">
                    <label for="username">E-mail</label>
                </div>

                <div class="input-field col s12">
                    <input autocomplete="off" required id="password" type="password" name="password" class="validate">
                    <label for="password">Adgangskode</label>
                </div>
            </div>

            <input name="log_in" type="hidden" value="1">

            <div class="row">
                <div class="input-field col s12">
                    <input class="tec-submit-btn" type="submit" value="Log ind">
                </div>
            </div>
        </form>
    </div>
</div>