<?php

declare(strict_types=1);

/**
 * Class Cronjobs
 * I denne klasse opretter vi én metode til hvert cronjob, vi gerne vil have der skal køre på TecTools siden
 */
class Cronjobs {
    public RCMS $RCMS;

    public function __construct(RCMS $RCMS) {
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
