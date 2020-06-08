<?php

/**
 * @var $TecTools TecTools
 */
$TecTools = $GLOBALS['TecTools'];

?>

<style>
    #add_manufacturer_form input, #add_manufacturer_form select {
        margin-bottom: 2rem;
    }
</style>

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h1 class="header center orange-text">Opret producent</h1>

        <div class="row center">
            <div class="col s12 m6 l6 xl6 offset-m3 offset-l3 offset-xl3">
                <form id="add_manufacturer_form" action="" method="POST">

                    <label>Navn</label>
                    <input id="manufacturer_name" required name="manufacturer_name" type="text" placeholder="Navn på producent">

                    <input type="hidden" name="add_manufacturer" value="1" />

                    <br><br>
                    <button id="add_manufacturer_btn" class="btn" type="submit">Opret producent</button>
                    <button class="btn" type="button" onclick="history.back()">Tilbage</button>

                </form>
            </div>
        </div>
        <br><br>
    </div>
</div>


