<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

ini_set( 'serialize_precision', '-1' );
date_default_timezone_set('Europe/Copenhagen');
setlocale(LC_ALL, 'da_DK');

require_once 'config.php';
require_once 'include/RCMS.php';

RCMS::fixURLQueryQuestionMarks();

if (ENVIRONMENT === 'development') {
    new RCMS(TEST_DB_HOST, TEST_DB_USER, TEST_DB_PASS, TEST_DB_NAME, ROOT_FOLDER, TEMPLATE_FOLDER_NAME, UPLOADS_FOLDER, TEST_STRIPE_SECRET_KEY);
} else if (ENVIRONMENT === 'production') {
    new RCMS(DB_HOST, DB_USER, DB_PASS, DB_NAME, ROOT_FOLDER, TEMPLATE_FOLDER_NAME, UPLOADS_FOLDER, STRIPE_SECRET_KEY);
} else {
    die('Fejl');
}



