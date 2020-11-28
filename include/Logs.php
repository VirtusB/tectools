<?php

/**
 * Class Logs
 * Denne klasse indeholder metoder der vedrører logging og hændelser
 * Den indeholder metoder til at oprette og hente logs
 * Den indeholder også konstanter over de forskellige typer af logs
 */
class Logs {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    public const SCAN_TYPE_ID = 1;
    public const CREATE_TOOL_TYPE_ID = 2;
    public const CREATE_CATEGORY_TYPE_ID = 3;
    public const CREATE_MANUFACTURER_TYPE_ID = 4;
    public const EDIT_TOOL_TYPE_ID = 5;
    public const EDIT_CATEGORY_TYPE_ID = 6;
    public const EDIT_MANUFACTURER_TYPE_ID = 7;
    public const LOG_IN_TYPE_ID = 8;
    public const LOG_OUT_TYPE_ID = 9;
    public const CHECK_IN_TYPE_ID = 10;
    public const NEW_SUBSCRIPTION_TYPE_ID = 11;
    public const CANCEL_SUBSCRIPTION_TYPE_ID = 12;
    public const UPGRADE_SUBSCRIPTION_TYPE_ID = 13;
    public const DOWNGRADE_SUBSCRIPTION_TYPE_ID = 14;
    public const DELETE_USER_TYPE_ID = 15;
    public const ADD_RESERVATION_TYPE_ID = 16;
    public const DELETE_RESERVATION_TYPE_ID = 17;
    public const ADD_COMMENT_TYPE_ID = 18;
    public const EDIT_COMMENT_TYPE_ID = 19;

    public function __construct(RCMS $RCMS) {
        $this->RCMS = $RCMS;
    }

    /**
     * Tilføjer en log om en hændelse i databasen
     * @param int $LogTypeID
     * @param array|null $data
     */
    public function addLog(int $LogTypeID, array $data = null) {
        if ($data === null) {
            if ($this->Login->isLoggedIn()) {
                $data = ['UserID' => $this->RCMS->Login->getUserID()];
            } else {
                $data = '';
            }
        }

        $json = empty($data) ? '' : json_encode($data);

        $this->RCMS->execute('CALL addLog(?, ?)', array('is', $LogTypeID, $json));
    }

    /**
     * Returnerer alle log typer fra databasen
     * @return array
     */
    public function getLogTypes(): array {
        return $this->RCMS->execute('CALL getLogTypes()')->fetch_all(MYSQLI_ASSOC) ?? [];
    }

    /**
     * Returnerer alle logs fra databasen
     * @return array
     */
    public function getLogs(): array {
        $logs = $this->RCMS->execute('CALL getLogs()')->fetch_all(MYSQLI_ASSOC) ?? [];

        foreach ($logs as &$log) {
            if (!is_object(json_decode($log['Data']))) {
                continue;
            }

            $log['Data'] = json_decode($log['Data'], true);
        }

        return $logs;
    }
}