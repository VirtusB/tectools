<?php

if (!isset($_GET['userid'])) {
    $this->RCMS->Functions->outputError('User ID mangler', 'h3', true);
}

/**
 * @var $TecTools TecTools
 */
$TecTools = $GLOBALS['TecTools'];

$user = $TecTools->getUserByID($_GET['userid']);

?>

<style>
    #edit_user_form input, #edit_user_form select {
        margin-bottom: 2rem;
    }
</style>

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h1 class="header center orange-text">Rediger bruger</h1>

        <div class="row center">
            <div class="col s12 m6 l6 xl6 offset-m3 offset-l3 offset-xl3">

                <form id="edit_user_form" action="" method="POST">

                    <label>Fornavn</label>
                    <input value="<?= $user['FirstName'] ?>" required name="firstname" type="text" placeholder="Fornavn på bruger">

                    <label>Efternavn</label>
                    <input value="<?= $user['LastName'] ?>" required name="lastname" type="text" placeholder="Efternavn på bruger">

                    <label>E-mail</label>
                    <input value="<?= $user['Email'] ?>" required name="email" type="email" placeholder="Brugerens email">

                    <label>Password</label>
                    <input name="password" type="text" placeholder="Brugerens password">

                    <label>Tlf. nr.</label>
                    <input value="<?= $user['Phone'] ?>" required name="phone" type="number" placeholder="Brugerens tlf. nr.">

                    <label>Adresse</label>
                    <input value="<?= $user['Address'] ?>" required name="address" type="text" placeholder="Brugerens adresse">

                    <label>Postnr.</label>
                    <input value="<?= $user['ZipCode'] ?>" required name="zipcode" type="number" placeholder="Brugerens postnr.">

                    <label>By</label>
                    <input value="<?= $user['City'] ?>" required name="city" type="text" placeholder="Brugerens by">

                    <label>Niveau</label>
                    <select required class="browser-default" name="level">
                        <option value="" disabled selected>Vælg brugertype</option>
                        <option <?= $user['Level'] === 1 ? 'selected' : '' ?> value="1">Standard</option>
                        <option <?= $user['Level'] === 9 ? 'selected' : '' ?> value="9">Administrator</option>
                    </select>

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



