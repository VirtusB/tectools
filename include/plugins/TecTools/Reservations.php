<?php

declare(strict_types=1);

/**
 * Class Reservations
 * Denne klasse indeholder metoder som vedrører reservationer på TecTools siden
 * Den indeholder metoder til bl.a. oprette og slette reservationer
 */
class Reservations {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    /**
     * @var bool $disableAutoLoading
     * Forhindrer RCMS at loade denne klasse automatisk
     */
    public static bool $disableAutoLoading;

    /**
     * Liste over POST endpoints (metoder), som kan eksekveres automatisk
     * Vi er nød til at have en liste over tilladte endpoints, så brugere ikke kan eksekvere alle metoder i denne klasse
     * @var array|string[]
     */
    public static array $allowedEndpoints = [
        'addReservation', 'deleteReservation'
    ];

    /**
     * @var TecTools $TecTools
     */
    public TecTools $TecTools;

    public function __construct(TecTools $TecTools) {
        $this->TecTools = $TecTools;
        $this->RCMS = $TecTools->RCMS;

        $TecTools->POSTClasses[] = $this;
    }

    /**
     * Tjekker om et værktøj er reserveret
     *
     * Returnerer false hvis det er brugeren selv, $userID, som ejer reservationen
     * @param int $toolID
     * @param int $userID
     * @return bool
     */
    public function isToolReserved(int $toolID, int $userID): bool {
        $res = $this->RCMS->execute('SELECT fn_isToolReserved(?, ?) AS isToolReserved', array('ii', $toolID, $userID));
        return (bool) $res->fetch_object()->isToolReserved;
    }

    /**
     * Returnerer hvor mange reservationer brugeren har
     * @param int $userID
     * @return int
     */
    public function getReservationCountForUser(int $userID): int {
        $res = $this->RCMS->execute('SELECT fn_getReservationCountForUser(?) AS count', array('i', $userID));
        return (int) $res->fetch_object()->count;
    }

    /**
     * Tjekker om en bruger har reserveret det antal værktøj som deres abonnement tillader
     * @param int $userID
     * @return bool
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function hasUserReachedMaxReservations(int $userID = 0): bool {
        if ($userID === 0) {
            $userID = $this->RCMS->Login->getUserID();
        }

        $userProduct = $this->TecTools->Users->getUserProduct($userID);

        if ($userProduct) {
            $maxReservations = (int) $userProduct['metadata']['MaxReservations']['value'];

            $userCurrentReservationCount = $this->getReservationCountForUser($userID);

            if ($userCurrentReservationCount < $maxReservations) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Sletter en reservation via POST request
     */
    public function deleteReservation(): void {
        $userID = $_POST['user_id'] ?? $this->RCMS->Login->getUserID();
        $userID = (int) $userID;
        $reservationID = (int) $_POST['reservation_id'];

        if (!$this->userOwnsReservation($reservationID) && !$this->RCMS->Login->isAdmin()) {
            Helpers::setNotification('Fejl', 'Du ejer ikke denne reservation', 'error');
            return;
        }

        $this->RCMS->execute('CALL removeReservation(?, ?)', array('ii', $userID, $reservationID));

        $this->RCMS->Logs->addLog(Logs::DELETE_RESERVATION_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Succes', 'Reservationen blev slettet');
    }

    /**
     * Tjekker om en bruger ejer en reservation
     * @param int $reservationID
     * @return bool
     */
    private function userOwnsReservation(int $reservationID): bool {
        $reservation = $this->RCMS->execute('CALL getReservationByID(?)', array('i', $reservationID))->fetch_array(MYSQLI_ASSOC);

        if (!$reservation) {
            return false;
        }

        if ($reservation['FK_UserID'] !== $this->RCMS->Login->getUserID()) {
            return false;
        }

        return true;
    }

    /**
     * Tilføjer en reservation for en bruger via POST request
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function addReservation(): void {
        $toolID = (int) $_POST['tool_id'];
        $userID = $this->RCMS->Login->getUserID();

        if ($this->RCMS->Login->isLoggedIn() === false) {
            Helpers::setNotification('Fejl', 'Du er ikke logget ind', 'error');
            return;
        }

        if ($this->TecTools->CheckIns->isToolCheckedIn($toolID) || $this->isToolReserved($toolID, $userID)) {
            Helpers::setNotification('Fejl', 'Værktøjet er allerede udlånt eller reserveret', 'error');
            return;
        }

        $userProduct = $this->TecTools->Users->getUserProduct($userID);
        if ($userProduct === false) {
            Helpers::setNotification('Fejl', 'Du har ikke noget abonnement', 'error');
            return;
        }

        if ($this->hasUserReachedMaxReservations($userID)) {
            Helpers::setNotification('Fejl', 'Du har allerede reserveret det antal værktøj som dit abonnement tillader', 'error');
            return;
        }

        // Alt validering foretaget
        // Tilføj reservation

        $reservationDuration = (int) $userProduct['metadata']['ReservationHours']['value'];

        $this->RCMS->execute('CALL addReservation(?, ?, ?)', array('iii', $userID, $toolID, $reservationDuration));

        $this->RCMS->Logs->addLog(Logs::ADD_RESERVATION_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Succes', 'Værktøjet er nu reserveret til dig');
    }
}