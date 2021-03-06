<?php

declare(strict_types=1);

/**
 * @var RCMS $this
 */

?>

<!-- Dark Theme style-->
<style id="dark-theme-style" <?= isset($_COOKIE['LS_THEME']) && $_COOKIE['LS_THEME'] === 'dark' ? '' : 'media="max-width: 1px"' ?> >
    body, .option-label {
        color: #d8d8d8;
        background-color: #121212;
        /*background-color: #06090F;*/
    }
    nav#header, #footer {
        background-color: #1d1d1d !important;
        /*background-color: #161B22 !important;*/
    }

    table.RCMSTable tr, #logs tr {
        border-bottom: 1px solid rgb(255, 255, 255);
    }

    .RCMSTable input.searchbar {
        color: #fff;
    }

    .log-type {
        background: #1d1d1d !important;
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
        /*background-color: #eeeeee24;*/
        background-color: #03363d;
    }

    .ql-editor.ql-blank::before {
        color: #fff !important;
    }

    div.modal {
        background: #1d1d1d !important;
    }

    div.modal textarea.materialize-textarea, #message {
        color: #eee;
    }

    input:not([type]):disabled, input:not([type])[readonly="readonly"], input[type=text]:not(.browser-default):disabled, input[type=text]:not(.browser-default)[readonly="readonly"], input[type=password]:not(.browser-default):disabled, input[type=password]:not(.browser-default)[readonly="readonly"], input[type=email]:not(.browser-default):disabled, input[type=email]:not(.browser-default)[readonly="readonly"], input[type=url]:not(.browser-default):disabled, input[type=url]:not(.browser-default)[readonly="readonly"], input[type=time]:not(.browser-default):disabled, input[type=time]:not(.browser-default)[readonly="readonly"], input[type=date]:not(.browser-default):disabled, input[type=date]:not(.browser-default)[readonly="readonly"], input[type=datetime]:not(.browser-default):disabled, input[type=datetime]:not(.browser-default)[readonly="readonly"], input[type=datetime-local]:not(.browser-default):disabled, input[type=datetime-local]:not(.browser-default)[readonly="readonly"], input[type=tel]:not(.browser-default):disabled, input[type=tel]:not(.browser-default)[readonly="readonly"], input[type=number]:not(.browser-default):disabled, input[type=number]:not(.browser-default)[readonly="readonly"], input[type=search]:not(.browser-default):disabled, input[type=search]:not(.browser-default)[readonly="readonly"], textarea.materialize-textarea:disabled, textarea.materialize-textarea[readonly="readonly"] {
        color: rgba(0,0,0,0.42);
        border-bottom: 1px dotted rgb(255 255 255 / 42%);
    }

    .tabs .tab a {
        color: #fff;
    }

    #dashboard-tabs {
        background: #1d1d1d;
    }

    .marquee-element {
        background: linear-gradient(249.01deg,#1d1d1d 1.42%,rgba(226,160,125,0)),#252527 !important;
    }

    .btn.cta {
        background: #2c3840 !important;
    }

    .empty-row, .store-row {
        background: #2C3840 !important;
    }

    .how-to-tectools.how-to-steps {
        background: #121212 !important;
        /*background: #06090F !important;*/
        color: #fff !important;
    }

    #faq, #faq .contentArea h3 {
        background-color: #1d1d1d;
    }

    #faq .heading3, #faq .section-title-large, #faq {
        color: #d8d8d8;
    }

    #faq .toggle:after {
        color: rgb(216 216 216 / 0.65);
    }

    .cms-page-view .std p, .cms-no-route .std p, .toggle-content p, .toggle-content span, .toggle-content strong, .col-wrapper-main p, .toggle-content ul {
        color: #d8d8d8 !important;
    }

    .btn.disabled, .disabled.btn-large, .disabled.btn-small, .btn-floating.disabled, .btn-large.disabled, .btn-small.disabled, .btn-flat.disabled, .btn:disabled, .btn-large:disabled, .btn-small:disabled, .btn-floating:disabled, .btn-large:disabled, .btn-small:disabled, .btn-flat:disabled, .btn[disabled], .btn-large[disabled], .btn-small[disabled], .btn-floating[disabled], .btn-large[disabled], .btn-small[disabled], .btn-flat[disabled] {
        background-color: #16191D !important;
    }
</style>

<script src="<?= $this->getTemplateFolder() ?>/js/themes.js"></script>