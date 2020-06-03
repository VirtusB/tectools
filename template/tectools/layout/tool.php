<?php

if (!isset($_GET['ToolID'])) {
    $this->RCMS->Functions->outputError('Tool ID mangler', 'h3', true);
}