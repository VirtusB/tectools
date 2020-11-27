<?php

declare(strict_types=1);

?>

<div class="container mt4">
    <div class="row">
        <div class="col s12 center mb2">
            <h4 class="bold mb2">Manual til personale</h4>
            <p class="mb2">Alle funktioner kræver at du er <a href="/login">logget ind</a></p>

            <button class="btn tec-btn play mb2">
                <i style="margin-right: 5px;" class="fad fa-play-circle"></i> Opret kategori
            </button>
            <div class="img-wrapper mb2">
                <img class="gif responsive-img" src="/template/tectools/images/create-category.gif" alt="">
            </div>

            <button class="btn tec-btn play mb2 mt2">
                <i style="margin-right: 5px;" class="fad fa-play-circle"></i> Opret værktøj
            </button>
            <div class="img-wrapper mb2">
                <img class="gif responsive-img" src="/template/tectools/images/create-tool.gif" alt="">
            </div>

            <button class="btn tec-btn play mb2 mt2">
                <i style="margin-right: 5px;" class="fad fa-play-circle"></i> Aktivitetscenter
            </button>
            <div class="img-wrapper mb2">
                <img class="gif responsive-img" src="/template/tectools/images/activity-center.gif" alt="">
            </div>

            <button class="btn tec-btn play mb2 mt2">
                <i style="margin-right: 5px;" class="fad fa-play-circle"></i> Tjek Ud via Dashboard
            </button>
            <div class="img-wrapper mb2">
                <img class="gif responsive-img" src="/template/tectools/images/check-out-tool-dashboard.gif" alt="">
            </div>

            <button class="btn tec-btn play mb2 mt2">
                <i style="margin-right: 5px;" class="fad fa-play-circle"></i> Tjek Ud via Scan
            </button>
            <div class="img-wrapper mb2">
                <img class="gif responsive-img" src="/template/tectools/images/check-out-tool-scan.gif" alt="">
            </div>

        </div>
    </div>
</div>

<link rel="stylesheet" href="<?= $this->RCMS->getTemplateFolder() ?>/css/manuals.css">

<script src="<?= $this->RCMS->getTemplateFolder() ?>/js/manuals/manuals.js"></script>