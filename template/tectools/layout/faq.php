<?php

declare(strict_types=1);

?>

<style>
    .hero-container:before {
        background-image: url(<?= $this->RCMS->getTemplateFolder() ?>/images/faq-small.png);
    }
</style>

<div class="container">
    <div class="hero-container">

    </div>

    <div class="row">

        <div class="main-content contentContainer">
            <div class="contentArea contentPadding">

                <h2 class="heading3" id="general">Generelt</h2>

                <h3 class="toggle solid-border section-title-large">Hvordan...</h3>
                <div class="toggle-content" style="">
                    <p>...</p>
                </div>

                <h3 class="toggle solid-border section-title-large">Hvor...</h3>
                <div class="toggle-content" style="">
                    <p>...</p>
                </div>

                <h3 class="toggle solid-border section-title-large">Hvad...</h3>
                <div class="toggle-content" style="">
                    <p>...</p>
                </div>

            </div>
        </div>
    </div>

</div>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/faq.css">

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/faq/faq.js"></script>




