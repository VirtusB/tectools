<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

ini_set( 'serialize_precision', '-1' );
date_default_timezone_set('Europe/Copenhagen');
//header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
//header("Cache-Control: post-check=0, pre-check=0", false);
//header("Pragma: no-cache");
//session_cache_limiter('nocache');

require_once 'config.php';
require_once 'include/RCMS.php';


RCMS::fixURLQueryQuestionMarks();

if (ENVIRONMENT === 'development') {
    new RCMS(TEST_DB_HOST, TEST_DB_USER, TEST_DB_PASS, TEST_DB_NAME, ROOT_FOLDER, TEMPLATE_FOLDER_NAME, UPLOADS_FOLDER, SALT, TEST_STRIPE_SECRET_KEY);
} else if (ENVIRONMENT === 'production') {
    new RCMS(DB_HOST, DB_USER, DB_PASS, DB_NAME, ROOT_FOLDER, TEMPLATE_FOLDER_NAME, UPLOADS_FOLDER, SALT, STRIPE_SECRET_KEY);
} else {
    die('Fejl');
}



