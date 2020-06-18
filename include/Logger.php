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
    }

    public function addPostLog(int $PostTypeID, array $data) {
        //$this->RCMS->execute('CALL addPostLog()')
    }

}