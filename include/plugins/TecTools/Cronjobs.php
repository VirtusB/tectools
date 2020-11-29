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
            $oldReservations = $this->RCMS->execute('CALL getOldReservations()')->fetch_all(MYSQLI_ASSOC) ?? [];

            foreach ($oldReservations as $reservation) {
                $this->sendMailOldReservationDeleted($reservation);
            }

            $this->RCMS->execute('CALL job_RemoveOldReservations()');
        };

        if (class_exists('Cron')) {
            $this->RCMS->getCron()->addCronJob(array("*/5 * * * *", $deleteOldReservations));
        }
    }

    /**
     * Sender en mail til brugeren, som forklarer at deres reservation er blevet slettet
     * @param array $reservation
     */
    private function sendMailOldReservationDeleted(array $reservation): void {
        $fullName = $reservation['FirstName'] . ' ' . $reservation['LastName'];
        $emailAddress = $reservation['Email'];
        $startDate = date('d-m-y H:i:s', strtotime($reservation['StartDate']));
        $endDate = date('d-m-y H:i:s', strtotime($reservation['EndDate']));
        $toolName = $reservation['ToolName'];
        $manufacturerName = $reservation['ManufacturerName'];
        $link = 'https://tectools.virtusb.com/tools/view?toolid=' . $reservation['FK_ToolID'];

        $body = <<<HTML
        <p>Kære $fullName</p>
        <p>Din reservation er blevet slettet, da reservationsperioden er udløbet.</p>
        <br>
        <h4>Reservationen:</h4>
        <p>Værktøj: $toolName</p>
        <p>Producent: $manufacturerName</p>
        <p>Start Dato: $startDate</p>
        <p>Slut Dato: $endDate</p>
        <p>Link: <a href="$link">Klik her for at se værktøjet</a></p>
        <br>
        <p>Med venlig hilsen TecTools</p>
        <img style="max-height: 53px" src="cid:TTLogo" alt="Logo" />
HTML;

        $logoPath = __DIR__ . '/../../../' . $this->RCMS->getTemplateFolder() . '/images/logo.png';

        Mailer::sendEmail(
            SMTP_USERNAME,
            'TecTools',
            $emailAddress,
            $fullName,
            'TecTools - reservation slettet',
            $body, [], [], 'TTLogo', $logoPath);
    }
}
