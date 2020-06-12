<?php

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

?>

<style>
    #add_category_form input, #add_category_form select {
        margin-bottom: 2rem;
    }
</style>

<div class="section no-pad-bot">
    <div class="container">
        <br><br>
        <h1 class="header center orange-text">Opret værktøj</h1>

        <div class="row center">
            <div class="col s12 m6 l6 xl6 offset-m3 offset-l3 offset-xl3">
                <form id="add_category_form" action="" method="POST">

                    <label>Navn</label>
                    <input id="tool_name" required name="category_name" type="text" placeholder="Navn på kategori">

                    <input type="hidden" name="add_category" value="1" />

                    <br><br>
                    <button id="add_category_btn" class="btn" type="submit">Opret kategori</button>
                    <button class="btn" type="button" onclick="history.back()">Tilbage</button>

                </form>
            </div>
        </div>
        <br><br>
    </div>
</div>


