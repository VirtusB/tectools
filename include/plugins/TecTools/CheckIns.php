<?php

declare(strict_types=1);

/**
 * Class CheckIns
 * Denne klasse indeholder metoder som vedrører Tjek Ind og Tjek Ud funktionalitet på TecTools siden
 * Den indeholder metoder til bl.a. låne værktøj og returnere værktøj på lager igen
 */
class CheckIns {
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
        'checkIn', 'checkOut', 'getCheckInComment', 'saveCheckInComment', 'getCheckInAjax'
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
     * Gemmer en kommentar for en udlejning via POST request
     */
    public function saveCheckInComment(): void {
        $checkInID = (int) $_POST['check_in_id'];
        $comment = $_POST['comment'];
        $userID = $this->RCMS->Login->getUserID();

        $checkIn = $this->getCheckIn($checkInID);

        if (!$checkIn) {
            Helpers::outputAJAXResult(400, ['result' => 'Udlejningen kunne ikke findes']);
        }

        if ($checkIn['FK_UserID'] !== $userID && !$this->RCMS->Login->isAdmin()) {
            Helpers::outputAJAXResult(400, ['result' => 'Du ejer ikke denne udlejning']);
        }

        $logType = empty($checkIn['Comment']) ? Logs::ADD_COMMENT_TYPE_ID : Logs::EDIT_COMMENT_TYPE_ID;
        $this->RCMS->Logs->addLog($logType, ['UserID' => $this->RCMS->Login->getUserID()]);

        $this->RCMS->execute('CALL saveCheckInComment(?, ?)', array('is', $checkInID, $comment));
        Helpers::outputAJAXResult(200, ['result' => 'OK']);
    }

    /**
     * Henter kommentaren for en specifik udlejning og udskriver den via POST request
     */
    public function getCheckInComment(): void {
        $checkInID = (int) $_POST['check_in_id'];
        $userID = $this->RCMS->Login->getUserID();

        $checkIn = $this->getCheckIn($checkInID);

        if (!$checkIn) {
            Helpers::outputAJAXResult(400, ['result' => 'Udlejningen kunne ikke findes']);
        }

        if ($checkIn['FK_UserID'] !== $userID && !$this->RCMS->Login->isAdmin()) {
            Helpers::outputAJAXResult(400, ['result' => 'Du ejer ikke denne udlejning']);
        }

        Helpers::outputAJAXResult(200, ['result' => ['Comment' => $checkIn['Comment'], 'CheckedOut' => $checkIn['CheckedOut']]]);
    }

    /**
     * Returnerer et CheckIn fra databasen via ID
     * @param $checkInID
     * @return array|false
     */
    private function getCheckIn(int $checkInID) {
        return $this->RCMS->execute('CALL getCheckIn(?)', array('i', $checkInID))->fetch_array(MYSQLI_ASSOC) ?? false;
    }

    /**
     * Returnerer et CheckIn fra databasen via stregkode
     * @param int $barcode
     * @return array|false
     */
    private function getCheckInByBarcode(int $barcode) {
        return $this->RCMS->execute('CALL getCheckInByBarcode(?)', array('i', $barcode))->fetch_array(MYSQLI_ASSOC) ?? false;
    }

    /**
     * Returnerer et CheckIn fra databasen via POST request
     */
    public function getCheckInAjax(): void {
        if (!$this->RCMS->Login->isAdmin()) {
            Helpers::outputAJAXResult(400, ['result' => 'Du er ikke en administrator']);
        }

        if (isset($_POST['check_in_id'])) {
            $checkInID = (int) $_POST['check_in_id'];
            $checkIn = $this->getCheckIn($checkInID);

            if (!$checkIn) {
                Helpers::outputAJAXResult(400, ['result' => 'Der er ikke nogen udlejning for dette værktøj']);
            }

            $tool = $this->TecTools->getToolByID($checkIn['FK_ToolID']);
        } else {
            $toolBarcode = (int) $_POST['tool_barcode'];
            $checkIn = $this->getCheckInByBarcode($toolBarcode);

            if (!$checkIn) {
                Helpers::outputAJAXResult(400, ['result' => 'Der er ikke nogen udlejning for dette værktøj']);
            }

            $tool = $this->TecTools->getToolByID($checkIn['FK_ToolID']);
            $tool['Image'] = $this->TecTools->cleanImagePath($tool['Image']);
        }

        $checkIn['tool'] = $tool;
        $checkIn['formattedStartDate'] = strftime ('d. %e %B kl. %H:%M:%S', strtotime($checkIn['StartDate']));
        $checkIn['formattedEndDate'] = strftime ('d. %e %B kl. %H:%M:%S', strtotime($checkIn['EndDate']));

        Helpers::outputAJAXResult(200, ['result' => $checkIn]);
    }

    /**
     * Tilføj et Check-In af et værktøj på en bruger, via POST request
     * @return void
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function checkIn(): void {
        $userID = $this->RCMS->Login->getUserID();

        $response = ['result' => ''];
        $barcode = $_POST['tool_barcode'] ?? null;

        if ($this->RCMS->Login->isLoggedIn() === false) {
            $response['result'] = 'Du er ikke logget ind';
            Helpers::outputAJAXResult(400, $response);
        }

        if (is_string($barcode) === false || strlen($barcode) !== 13) {
            $response['result'] = 'Stregkode er ikke 13 karakterer';
            Helpers::outputAJAXResult(400, $response);
        }

        $tool = $this->TecTools->getToolByBarcode($barcode);
        if (empty($tool)) {
            $response['result'] = 'Intet værktøj fundet med den stregkode';
            Helpers::outputAJAXResult(400, $response);
        }

        $toolID = $tool['ToolID'];
        if ($this->isToolCheckedIn($toolID) || $this->TecTools->Reservations->isToolReserved($toolID, $userID)) {
            $response['result'] = 'Værktøjet er allerede udlånt eller reserveret';
            Helpers::outputAJAXResult(400, $response);
        }

        $userProduct = $this->TecTools->Users->getUserProduct($userID);
        if ($userProduct === false) {
            $response['result'] = 'Du har ikke noget abonnement';
            Helpers::outputAJAXResult(400, $response);
        }

        if ($this->hasUserReachedMaxCheckouts($userID)) {
            $response['result'] = 'Du har allerede udlånt det antal værktøj som dit abonnement tillader';
            Helpers::outputAJAXResult(400, $response);
        }

        // Alt validering foretaget
        // Tilføj checkin

        $checkInDuration = (int) $userProduct['metadata']['MaxCheckoutDays']['value'];

        $this->RCMS->execute('CALL addCheckIn(?, ?, ?)', array('iii', $userID, $toolID, $checkInDuration));

        $this->RCMS->Logs->addLog(Logs::CHECK_IN_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        $response['result'] = 'success';
        Helpers::outputAJAXResult(200, $response);
    }

    /**
     * Tjekker om en bruger har udlånt det antal værktøj som deres abonnement tillader
     * @param int $userID
     * @return bool
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function hasUserReachedMaxCheckouts(int $userID = 0): bool {
        if ($userID === 0) {
            $userID = $this->RCMS->Login->getUserID();
        }

        $userProduct = $this->TecTools->Users->getUserProduct($userID);

        if ($userProduct) {
            $maxCheckOuts = (int) $userProduct['metadata']['MaxCheckouts']['value'];

            $userCurrentCheckOut = $this->getCheckInCountForUser($userID);

            if ($userCurrentCheckOut < $maxCheckOuts) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Tjekker et værktøj ud via POST request
     * Kan kun bruges af administratorer
     */
    public function checkOut(): void {
        $checkInID = (int) $_POST['check_in_id'];
        $statusID = $_POST['status_id'];

        if (!$this->RCMS->Login->isAdmin()) {
            Helpers::setNotification('Fejl', 'Du er ikke en administrator', 'error');
            return;
        }

        $checkIn = $this->getCheckIn($checkInID);
        $tool = $this->TecTools->getToolByID($checkIn['FK_ToolID']);
        $toolID = $tool['ToolID'];

        $this->RCMS->execute('CALL checkout(?, ?)', array('ii', $toolID, $statusID));

        Helpers::setNotification('Succes', 'Værktøjet blev tjekket ud');
    }

    /**
     * Tjekker om et værktøj er udlånt
     * @param int $toolID
     * @return bool
     */
    public function isToolCheckedIn(int $toolID): bool {
        $res = $this->RCMS->execute('SELECT fn_isToolCheckedIn(?) AS isToolCheckedIn', array('i', $toolID));
        return (bool) $res->fetch_object()->isToolCheckedIn;
    }

    /**
     * Returnerer hvor mange udlån brugeren har
     * @param int $userID
     * @return int
     */
    public function getCheckInCountForUser(int $userID): int {
        $res = $this->RCMS->execute('SELECT fn_getCheckInCountForUser(?) AS TOOL_COUNT', array('i', $userID));
        return (int) $res->fetch_object()->TOOL_COUNT;
    }

    /**
     * Returnerer udlejninger som skal vises nederst på forsiden i det glidende element, marquee
     * @return array
     */
    public function getCheckInsForMarquee(): array {
        $checkIns = $this->RCMS->execute('CALL getCheckInsForMarquee()')->fetch_all(MYSQLI_ASSOC) ?? [];

        foreach ($checkIns as &$checkIn) {
            $checkIn['Image'] = $this->TecTools->cleanImagePath($checkIn['Image']);
        }

        return $checkIns;
    }
}