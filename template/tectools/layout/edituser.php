<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

if (!isset($_GET['userid']) || !is_numeric($_GET['userid'])) {
    $this->RCMS->Functions->outputError('User ID mangler', 'h3', true);
    return;
}

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

$userID = (int) $_GET['userid'];

if (!$TecTools->authorizeUser($userID)) {
    $this->RCMS->Functions->outputError('Du har ikke adgang til denne side', 'h3', true);
    return;
}

$user = $TecTools->getUserByID($userID);

?>

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h1 class="header center orange-text">Rediger bruger</h1>

        <?php
        if (isset($_GET['emailtaken'])) {
            $this->RCMS->Functions->outputError('Bruger med den email eksisterer allerede', 'h5', true);
        }
        ?>

        <div class="row center">
            <div class="col s12 m6 l6 xl6 offset-m3 offset-l3 offset-xl3">

                <form class="tectool-form" id="edit_user_form" action="" method="POST">

                    <label>Fornavn</label>
                    <input value="<?= $user['FirstName'] ?>" required name="firstname" type="text" placeholder="Fornavn">

                    <label>Efternavn</label>
                    <input value="<?= $user['LastName'] ?>" required name="lastname" type="text" placeholder="Efternavn">

                    <label>E-mail</label>
                    <input value="<?= $user['Email'] ?>" required name="email" type="email" placeholder="Email">

                    <label>Adgangskode</label>
                    <input name="password" type="text" placeholder="Adgangskode">

                    <label>Tlf. nr.</label>
                    <input value="<?= $user['Phone'] ?>" required name="phone" type="number" placeholder="Tlf. nr.">

                    <label>Adresse</label>
                    <input value="<?= $user['Address'] ?>" required name="address" type="text" placeholder="Adresse">

                    <label>Postnr.</label>
                    <input value="<?= $user['ZipCode'] ?>" required name="zipcode" type="number" placeholder="Postnr.">

                    <label>By</label>
                    <input value="<?= $user['City'] ?>" required name="city" type="text" placeholder="By">

                    <?php if ($this->RCMS->Login->isAdmin()): ?>
                    <label>Niveau</label>
                    <select required class="browser-default" name="level">
                        <option value="" disabled selected>Vælg brugertype</option>
                        <option <?= $user['Level'] === 1 ? 'selected' : '' ?> value="1">Standard</option>
                        <option <?= $user['Level'] === 9 ? 'selected' : '' ?> value="9">Personale</option>
                    </select>
                    <?php endif; ?>

                    <input type="hidden" name="edit_user" value="1" />

                    <input type="hidden" name="user_id" value="<?= $user['UserID'] ?>">

                    <br><br>
                    <button id="edit_user_btn" class="btn" type="submit">Gem</button>
                    <button class="btn" type="button" onclick="history.back()">Tilbage</button>

                </form>
            </div>
        </div>
        <br><br>
    </div>
</div>



