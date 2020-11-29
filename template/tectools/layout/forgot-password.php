<?php

declare(strict_types=1);

?>

<div class="container mt4">
    <div class="row">
        <h1 class="center">Glemt adgangskode</h1>
        <h6 class="center">Vælg ny adgangskode</h6>

        <?php if (isset($_GET['sent'])): // Efter link er sendt til personen ?>
            <div class="row" style="display: flex">
                <div class="col s12 m5" style="margin: 0 auto;">
                    <div class="card-panel teal">
                <span class="white-text">
                    Et link er blevet sendt til din e-mail. <br> Klik på linket for at vælge en ny adgangskode.
                </span>
                    </div>
                </div>
            </div>
        <?php elseif (isset($_GET['hash'])): // Når personen har klikket på linket ?>
            <form method="post"  enctype="multipart/form-data" class="col s12 m4 offset-m4">

                <div class="row mt4">
                    <div class="input-field col s12">
                        <input required value="" id="password" name="password" type="password" class="validate">
                        <label for="password">Ny adgangskode</label>
                    </div>

                    <div class="input-field col s12">
                        <input required value="" id="repeat_password" name="repeat_password" type="password" class="validate">
                        <label for="repeat_password">Gentag adgangskode</label>
                    </div>
                </div>

                <input name="resetPasswordVerify" type="hidden" value="1">
                <input type="hidden" name="hash" value="<?= $_GET['hash'] ?>">

                <div class="row">
                    <div class="input-field col s12">
                        <input class="btn tec-submit-btn" type="submit" value="Gem">
                    </div>
                </div>
            </form>
        <?php else: ?>

            <form method="post"  enctype="multipart/form-data" class="col s12 m4 offset-m4">
                <div class="row mt4">
                    <div class="input-field col s12">
                        <input required value="" id="email" name="email" type="text" class="validate">
                        <label for="email">E-mail adresse</label>
                    </div>
                </div>

                <input name="resetPassword" type="hidden" value="1">

                <div class="row">
                    <div class="input-field col s12">
                        <input class="btn tec-submit-btn" type="submit" value="Gendan">
                    </div>
                </div>
            </form>

        <?php endif; ?>
    </div>
</div>
