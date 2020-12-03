<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../include/RCMS.php';

//require_once './tectools/config.php';
//require_once './tectools/include/RCMS.php';

/**
 * @param bool $seed
 * @param bool $returnRCMS
 * @return RCMS|void
 */
function setupTestDatabase(bool $seed = false, bool $returnRCMS = true) {
    // Dump strukturen for produktions databasen ud, uden data, funktioner og procedures
    $dumpDatabaseCommand = sprintf('mysqldump -u %s -p%s --no-data %s > %s', DB_USER, DB_PASS, DB_NAME, DB_DUMP_SQL_FILENAME);
    shell_exec($dumpDatabaseCommand);

    // Opret tabellerne i test databasen
    $createTestDatabase = sprintf('mysql -u %s -p%s %s < %s', TEST_DB_USER, TEST_DB_PASS, TEST_DB_NAME, DB_DUMP_SQL_FILENAME);
    shell_exec($createTestDatabase);

    // Hent funktioner og procedures ud af produktions databasen
    $RCMS = new RCMS(DB_HOST, DB_USER, DB_PASS, DB_NAME, ROOT_FOLDER, TEMPLATE_FOLDER_NAME, UPLOADS_FOLDER, STRIPE_SECRET_KEY, 'development');

    $res = $RCMS->execute('CALL getAllProcedures()');

    $procedures = $res->fetch_all(MYSQLI_ASSOC);

    $proceduresAndFunctions = [];

    foreach ($procedures as $procedure) {
        $name = $procedure['Name'];
        $type = $procedure['Type'];

        $res = $RCMS->execute('CALL getProcedureCreateCode(?, ?)', array('ss', $name, $type));

        $createCode = $res->fetch_array(MYSQLI_ASSOC);
        $codeColumn = 'Create ' . ucfirst(strtolower($type));

        $proceduresAndFunctions[] = [
            'Code' => $createCode[$codeColumn],
            'Name' => $name,
            'Type' => strtoupper($type)
        ];
    }

    $RCMS->closeRCMS();
    unset($RCMS);

    // Indsæt funktioner og procedures ind i test databasen
    $RCMS = new RCMS(TEST_DB_HOST, TEST_DB_USER, TEST_DB_PASS, TEST_DB_NAME, ROOT_FOLDER, TEMPLATE_FOLDER_NAME, UPLOADS_FOLDER, TEST_STRIPE_SECRET_KEY, 'development');

    mysqli_set_charset($RCMS->getMySQLi(), 'utf8mb4');

    foreach ($proceduresAndFunctions as $prodOrFunc) {
        mysqli_query($RCMS->getMySQLi(), "DROP {$prodOrFunc['Type']} IF EXISTS {$prodOrFunc['Name']};");

        $code = $prodOrFunc['Code'];
        $code = str_replace(DB_NAME, TEST_DB_NAME, $code);

        $code = str_replace('DEFINER=`virtusbc_tectools_test`@`%`', '', $code);

        $res = mysqli_multi_query($RCMS->getMySQLi(), $code);

        if ($res === false) {
            echo '<br><br>' . mysqli_error($RCMS->getMySQLi()) . '<br><br>';
        }
    }


    if (!$returnRCMS) {
        $RCMS->closeRCMS();
        return;
    }

    return $RCMS;
}

