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

    .log-type {
        background: #1d1d1d !important;
    }

    #des-editor div.ql-editor {
        /*background: #909090;*/
    }

    .card-panel.teal {
        background-color: #1d1d1d !important;
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

    input:not([type]):disabled, input:not([type])[readonly="readonly"], input[type=text]:not(.browser-default):disabled, input[type=text]:not(.browser-default)[readonly="readonly"], input[type=password]:not(.browser-default):disabled, input[type=password]:not(.browser-default)[readonly="readonly"], input[type=email]:not(.browser-default):disabled, input[type=email]:not(.browser-default)[readonly="readonly"], input[type=url]:not(.browser-default):disabled, input[type=url]:not(.browser-default)[readonly="readonly"], input[type=time]:not(.browser-default):disabled, input[type=time]:not(.browser-default)[readonly="readonly"], input[type=date]:not(.browser-default):disabled, input[type=date]:not(.browser-default)[readonly="readonly"], input[type=datetime]:not(.browser-default):disabled, input[type=datetime]:not(.browser-default)[readonly="readonly"], input[type=datetime-local]:not(.browser-default):disabled, input[type=datetime-local]:not(.browser-default)[readonly="readonly"], input[type=tel]:not(.browser-default):disabled, input[type=tel]:not(.browser-default)[readonly="readonly"], input[type=number]:not(.browser-default):disabled, input[type=number]:not(.browser-default)[readonly="readonly"], input[type=search]:not(.browser-default):disabled, input[type=search]:not(.browser-default)[readonly="readonly"], textarea.materialize-textarea:disabled, textarea.materialize-textarea[readonly="readonly"] {
        color: rgba(0,0,0,0.42);
        border-bottom: 1px dotted rgb(255 255 255 / 42%);
    }
</style>

<script src="<?= $this->getTemplateFolder() ?>/js/themes.js"></script>