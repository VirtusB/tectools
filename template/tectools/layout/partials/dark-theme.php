<?php

declare(strict_types=1);

/**
 * @var RCMS $this
 */

?>

<!-- Dark Theme style-->
<style id="dark-theme-style" media="max-width: 1px">
    body, .option-label {
        color: #d8d8d8;
        background-color: #121212;
    }
    nav#header, #footer {
        background-color: #1d1d1d !important;
    }

    table.RCMSTable tr {
        border-bottom: 1px solid rgb(255, 255, 255);
    }

    .RCMSTable input.searchbar {
        color: #fff;
    }

    select {
        color: #fff;
        background-color: #1d1d1d;
    }

    input {
        color: #fff;
    }

    .card {
        background: #1d1d1d;
        color: #fff;
    }

    .card span.card-title, .card span.card-title.black-text {
        color: #fff !important;
    }

    .collection .collection-item {
        background-color: #1d1d1d;
    }

    #tool-image-col {
        border: 2px solid #1D1D1D !important;
    }

    .info-paragraph {
        background-color: #eeeeee24;
    }

    .ql-editor.ql-blank::before {
        color: #fff !important;
    }

    div.modal {
        background: #1d1d1d !important;
    }

    div.modal textarea.materialize-textarea {
        color: #eee;
    }
</style>

<script src="<?= $this->getTemplateFolder() ?>/js/themes.js"></script>