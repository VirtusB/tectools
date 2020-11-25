<?php

class Cronjobs {
    public RCMS $RCMS;

    public function __construct($RCMS) {
        $this->RCMS = $RCMS;

        $this->deleteOldReservations();
    }

    /**
     * Tilføjer cronjob til at slette gamle reservationer
     * Køres hvert 5. minut
     */
    public function deleteOldReservations(): void {
        $deleteOldReservations = function() {
            $this->RCMS->execute('CALL job_RemoveOldReservations()');
        };

        if (class_exists('Cron')) {
            $this->RCMS->getCron()->addCronJob(array("*/5 * * * *", $deleteOldReservations));
        }
    }
}
