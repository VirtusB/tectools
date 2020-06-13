<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

if ($this->RCMS->Login->isLoggedIn()) {
    header('Location: /dashboard');
}
?>

<div class="row">
    <div class="col s12 m8 l4 xl2 offset-m2 offset-l4 offset-xl5">
        <div id="">
            <h1>Log ind</h1>

            <?php
            if (isset($_GET['wrong_email_or_password'])) {
                $this->RCMS->Functions->outputError('Forkert email eller adgangskode', 'h6');
            }

            if (isset($_GET['userInfoChanged'])) {
                echo '<h6>Da du har ændret dine brugerinformationer, skal du logge ind igen</h6>';
            }
            ?>

            <form action="" method="POST">
                <input autocomplete="off" name="email" type="email" required placeholder="E-mail"><br>
                <input autocomplete="off" name="password" type="password" required placeholder="Adgangskode"><br>
                <input name="log_in" type="hidden" value="1"><br>
                <input class="btn" type="submit" value="Log ind">
            </form>
            <br><br><br>
        </div>
    </div>
</div>