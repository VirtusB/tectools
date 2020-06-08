<?php// UTF-8 encoding setup$this->Template->initEncoding();?><!DOCTYPE html><html lang="da"><head>    <meta charset="UTF-8">    <meta name="viewport"          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">    <meta http-equiv="Content-type" content="text/html;charset=UTF-8">    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>    <link rel="icon" type="image/png" href="<?= $this->getTemplateFolder() ?>/icons/favicon.ico">    <link rel="apple-touch-icon" sizes="180x180" href="<?= $this->getTemplateFolder() ?>/icons/apple-touch-icon.png">    <link rel="icon" type="image/png" sizes="32x32" href="<?= $this->getTemplateFolder() ?>/icons/favicon-32x32.png">    <link rel="icon" type="image/png" sizes="16x16" href="<?= $this->getTemplateFolder() ?>/icons/favicon-16x16.png">    <link rel="manifest" href="<?= $this->getTemplateFolder() ?>/icons/site.webmanifest">    <meta name="msapplication-TileColor" content="#8cc63e">    <meta name="theme-color" content="#8cc63e">    <title>Tec Tools</title>    <link type="text/css" rel="stylesheet" href="<?= $this->getTemplateFolder() ?>/materialize/css/materialize.min.css">    <link type="text/css" rel="stylesheet" href="<?= $this->getTemplateFolder() ?>/fontawesome/css/solid.css">    <link type="text/css" rel="stylesheet" href="<?= $this->getTemplateFolder() ?>/fontawesome/css/fontawesome.css">    <link rel="stylesheet" href="<?= $this->getTemplateFolder() ?>/css/main.css" type="text/css">    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">    <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">    <script src="<?= $this->getTemplateFolder() ?>/js/libs/JsBarcode.all.min.js"></script></head><body><?php require_once __DIR__ . '/layout/partials/header.php'; ?><!-- Dark Theme style--><style id="dark-theme-style" media="max-width: 1px">    body, .option-label {        color: #d8d8d8;        background-color: #121212;    }    #nav, #footer {        background-color: #1d1d1d !important;    }    table.RCMSTable tr {        border-bottom: 1px solid rgb(255, 255, 255);    }    .RCMSTable input.searchbar {        color: #fff;    }    select {        color: #fff;        background-color: #1d1d1d;    }    input {        color: #fff;    }    .card {        background: #1d1d1d;        color: #fff;    }    .card span.card-title, .card span.card-title.black-text {        color: #fff !important;    }</style><script src="<?= $this->getTemplateFolder() ?>/js/themes.js"></script><main>    <?php    //if ($this->Functions->isScalePage()) {    //    require_once __DIR__ . '/layout/scalepage.php';    //} else {    //    $this->Template->display_content();    //}    $this->Template->display_content();    ?></main><script src="<?= $this->getTemplateFolder() ?>/js/helpers.js"></script><script src="<?= $this->getTemplateFolder() ?>/js/libs/jquery.min.js"></script><script src="<?= $this->getTemplateFolder() ?>/materialize/js/materialize.min.js"></script><script src="<?= $this->getTemplateFolder() ?>/js/main.js"></script><?php require_once __DIR__ . '/layout/partials/footer.php'; ?></body></html>