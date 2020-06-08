<?php

if (!isset($_GET['toolid'])) {
    $this->RCMS->Functions->outputError('Tool ID mangler', 'h3', true);
}