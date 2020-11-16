<?php

declare(strict_types=1);

/**
 * @var Template $this
 */

if (!isset($_GET['userid']) || !is_numeric($_GET['userid'])) {
    Functions::outputError('User ID mangler', 'h3', true);
    return;
}

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

$userID = (int) $_GET['userid'];

if (!$TecTools->authorizeUser($userID)) {
    Functions::outputError('Du har ikke adgang til denne side', 'h3', true);
    return;
}

$user = $TecTools->getUserByID($userID);

?>

<div class="container mt4">
    <div class="row">
        <form method="post"  class="col s12 m6 offset-m3 tectool-form">
            <h1 class="center mb4 mt0">Rediger bruger</h1>

            <div style="text-align: center">
                <?php
                if (isset($_GET['emailtaken'])) {
                    Functions::outputError('Bruger med den email eksisterer allerede', 'h6');
                }
                ?>
            </div>

            <div class="row mt2 mb0">
                <div class="input-field col s6">
                    <input autocomplete="off" required value="<?= $user['FirstName'] ?>" id="firstname" name="firstname" type="text" class="validate">
                    <label for="firstname">Fornavn</label>
                </div>

                <div class="input-field col s6">
                    <input autocomplete="off" required value="<?= $user['LastName'] ?>" id="lastname" name="lastname" type="text" class="validate">
                    <label for="lastname">Efternavn</label>
                </div>

                <div class="input-field col s12">
                    <input autocomplete="off" required value="<?= $user['Address'] ?>" id="address" name="address" type="text" class="validate">
                    <label for="address">Adresse</label>
                </div>

                <div class="input-field col s6">
                    <input pattern="\d*" minlength="4" maxlength="4" autocomplete="off" required value="<?= $user['ZipCode'] ?>" id="zipcode" name="zipcode" type="text" class="validate">
                    <label for="zipcode">Postnr.</label>
                </div>

                <div class="input-field col s6">
                    <input autocomplete="off" required value="<?= $user['City'] ?>" id="city" name="city" type="text" class="validate">
                    <label for="city">By</label>
                </div>

                <div class="input-field col s6">
                    <input autocomplete="off" id="email" value="<?= $user['Email'] ?>" name="email" type="email" required class="validate">
                    <label for="email">E-mail</label>
                </div>

                <div class="input-field col s6">
                    <input pattern="\d*" minlength="8" maxlength="8" autocomplete="off" required value="<?= $user['Phone'] ?>" id="phone" name="phone" type="text" class="validate">
                    <label for="phone">Tlf. nr.</label>
                </div>

                <div class="input-field col s12">
                    <input autocomplete="new-password" id="password" name="password" type="password" class="validate">
                    <label for="password">Adgangskode</label>
                </div>

                <?php if ($this->RCMS->Login->isAdmin()): ?>
                    <div class="input-field col s6">
                        <select id="user-level" required  name="level">
                            <option value="" disabled selected>Vælg brugertype</option>
                            <option <?= $user['Level'] === 1 ? 'selected' : '' ?> value="1">Standard</option>
                            <option <?= $user['Level'] === 9 ? 'selected' : '' ?> value="9">Personale</option>
                        </select>
                        <label for="user-level">Niveau</label>
                    </div>
                <?php endif; ?>

            </div>

            <input type="hidden" name="edit_user" value="1" />

            <input type="hidden" name="user_id" value="<?= $user['UserID'] ?>">

            <div class="row mb0">
                <div class="input-field col s12">
                    <input class="tec-submit-btn" type="submit" value="Gem">
                </div>
            </div>

            <div class="row mb0">
                <div class="input-field col s6 m0">
                    <button class="btn tec-btn" type="button" onclick="history.back()">Tilbage</button>
                </div>
            </div>

            <div class="row">
                <div class="input-field col s6 m0">
                    <button class="btn tec-btn red" type="button">Slet bruger</button>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('select').formSelect();
    });
</script>

