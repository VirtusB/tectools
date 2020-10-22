<?php

declare(strict_types=1);

class Logger {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    public const SCAN_POST_TYPE_ID = 1;

    public function __construct(RCMS $RCMS) {
        $this->RCMS = $RCMS;

        //$this->setupLogging();
    }

    private function setupLogging() {
        if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
            return;
        }

        $loggedIn = $this->RCMS->Login->isLoggedIn();

        $data = ['logged_in' => $loggedIn];

        if ($loggedIn === true) {
            $data['user_id'] = $this->RCMS->Login->getUserID();
        }

        $data['post'] = $_POST;


    }

    private function addPostLog(int $PostTypeID, array $data) {
        //$this->RCMS->execute('CALL addPostLog()')
    }

}