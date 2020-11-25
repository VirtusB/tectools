<?php

declare(strict_types=1);

/**
 * @var TecTools $TecTools
 */
$TecTools = $GLOBALS['TecTools'];

?>

<div class="container mt4">
    <div class="row">
        <form method="post"  class="col s12 m6 offset-m3 tectool-form">
            <h1 class="center mb4 mt0">Opret kategori</h1>

            <div class="row mt2 mb0">
                <div class="input-field col s12">
                    <input required id="category_name" name="category_name" type="text" class="validate">
                    <label for="category_name">Navn</label>
                </div>
            </div>

            <input type="hidden" name="post_endpoint" value="addCategory" />

            <div class="row mb0">
                <div class="input-field col s12">
                    <input class="tec-submit-btn" type="submit" value="Opret kategori">
                </div>
            </div>

            <div class="row mb0">
                <div class="input-field col s6 m0">
                    <button class="btn tec-btn" type="button" onclick="location.href = '/dashboard'">Tilbage</button>
                </div>
            </div>

        </form>
    </div>
</div>


