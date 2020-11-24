<?php

declare(strict_types=1);

class TecTools {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    /**
     * Absolutte sti til mappen hvor billeder af værktøj ligger
     * @var string $TOOL_IMAGE_FOLDER
     */
    public string $TOOL_IMAGE_FOLDER;

    /**
     * Relative sti til mappen hvor billeder af værktøj ligger
     * @var string $RELATIVE_TOOL_IMAGE_FOLDER
     */
    public string $RELATIVE_TOOL_IMAGE_FOLDER;

    /**
     * Antal værktøj der bliver vist på forsiden per side
     */
    private const TOOLS_PER_PAGE = 8;

    /**
     * Status værdi for værktøj som er på lager
     */
    public const TOOL_AVAILABLE_STATUS = 1;

    /**
     * Status værdi for værktøj som er reserveret
     */
    public const TOOL_RESERVED_STATUS = 2;

    /**
     * Status værdi for værktøj som er udlånt
     * @var int TOOL_AVAILABLE_STATUS
     */
    public const TOOL_LOANED_OUT_STATUS = 3;

    /**
     * Status værdi for værktøj som ikke er på lager (ex. demo vare, sendt til reparation, udgået)
     * @var int TOOL_AVAILABLE_STATUS
     */
    public const TOOL_NOT_IN_STOCK_STATUS = 4;

    /**
     * Liste over POST endpoints, som har en metode i denne klase, som kan eksekveres automatisk
     * Vi er nød til at have en liste over tilladte endpoints, så brugere ikke kan eksekvere andre metoder i denne klasse
     * @var array|string[]
     */
    private static array $allowedEndpoints = [
        'addTool', 'editTool',
        'addCategory', 'editCategory',
        'addManufacturer', 'editManufacturer',
        'editUser',
        'checkIn', 'getCheckInComment', 'saveCheckInComment',
        'getToolByBarcodeAjax',
        'newSubscription', 'cancelSubscription', 'upgradeDowngradeSubscription',
        'deleteUser',
        'addReservation', 'deleteReservation'
    ];

    public function __construct(RCMS $RCMS) {
        $this->RCMS = $RCMS;
        $this->TOOL_IMAGE_FOLDER = $this->RCMS->getUploadsFolder() . '/tools/images';
        $this->RELATIVE_TOOL_IMAGE_FOLDER = $this->RCMS->getRelativeUploadsFolder() . '/tools/images';

        $this->handlePOSTEndpoints();
    }

    /**
     * Denne metode tjekker, om $_POST array'et indeholder navnet på en metode i denne klasse,
     * tjekker derefter om det er en af de tilladte endpoints,
     * og eksekvere efterfølgende metoden hvis det er tilfældet
     */
    private function handlePOSTEndpoints(): void {
        if (!isset($_POST['post_endpoint'])) {
            return;
        }

        $endpoint = $_POST['post_endpoint'];

        if (method_exists($this, $endpoint) && in_array($endpoint, self::$allowedEndpoints, true)) {
            $this->$endpoint();
        }
    }

    /**
     * Gemmer en kommentar for en udlejning
     */
    private function saveCheckInComment(): void {
        $checkInID = $_POST['check_in_id'];
        $comment = $_POST['comment'];
        $userID = $this->RCMS->Login->getUserID();

        $checkIn = $this->getCheckIn($checkInID);

        if (!$checkIn) {
            Functions::outputAJAXResult(400, ['message' => 'Udlejningen kunne ikke findes']);
        }

        if ($checkIn['FK_UserID'] !== $userID && !$this->RCMS->Login->isAdmin()) {
            Functions::outputAJAXResult(400, ['message' => 'Du ejer ikke denne udlejning']);
        }

        $logType = empty($checkIn['Comment']) ? LogTypes::ADD_COMMENT_TYPE_ID : LogTypes::EDIT_COMMENT_TYPE_ID;
        $this->RCMS->addLog($logType, ['UserID' => $this->RCMS->Login->getUserID()]);

        $this->RCMS->execute('CALL saveCheckInComment(?, ?)', array('is', $checkInID, $comment));
        Functions::outputAJAXResult(200, ['OK']);
    }

    /**
     * Henter kommentaren for en specifik udlejning og udskriver den via POST request
     */
    private function getCheckInComment(): void {
        $checkInID = $_POST['check_in_id'];
        $userID = $this->RCMS->Login->getUserID();

        $checkIn = $this->getCheckIn($checkInID);

        if (!$checkIn) {
            Functions::outputAJAXResult(400, ['message' => 'Udlejningen kunne ikke findes']);
        }

        if ($checkIn['FK_UserID'] !== $userID && !$this->RCMS->Login->isAdmin()) {
            Functions::outputAJAXResult(400, ['message' => 'Du ejer ikke denne udlejning']);
        }

        Functions::outputAJAXResult(200, ['comment' => $checkIn['Comment']]);
    }

    /**
     * Returnerer et CheckIn fra databasen
     * @param $checkInID
     * @return array|false
     */
    private function getCheckIn($checkInID) {
        return $this->RCMS->execute('CALL getCheckIn(?)', array('i', $checkInID))->fetch_array(MYSQLI_ASSOC) ?? false;
    }

    /**
     * Sletter en reservation via POST request
     */
    private function deleteReservation(): void {
        $reservationID = (int) $_POST['reservation_id'];

        if (!$this->userOwnsReservation($reservationID)) {
            Functions::setNotification('Fejl', 'Du ejer ikke denne reservation', 'error');
            return;
        }

        $userID = $this->RCMS->Login->getUserID();

        $this->RCMS->execute('CALL removeReservation(?, ?)', array('ii', $userID, $reservationID));

        $this->RCMS->addLog(LogTypes::DELETE_RESERVATION_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::setNotification('Success', 'Reservationen blev slettet');
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
    private function addReservation(): void {
        $toolID = (int) $_POST['tool_id'];
        $userID = $this->RCMS->Login->getUserID();

        if ($this->RCMS->Login->isLoggedIn() === false) {
            Functions::setNotification('Fejl', 'Du er ikke logget ind', 'error');
            return;
        }

        if ($this->isToolCheckedIn($toolID) || $this->isToolReserved($toolID, $userID)) {
            Functions::setNotification('Fejl', 'Værktøjet er allerede udlånt eller reserveret', 'error');
            return;
        }

        $userProduct = $this->getUserProduct($userID);
        if ($userProduct === false) {
            Functions::setNotification('Fejl', 'Du har ikke noget abonnement', 'error');
            return;
        }

        if ($this->hasUserReachedMaxReservations($userID)) {
            Functions::setNotification('Fejl', 'Du har allerede reserveret det antal værktøj som dit abonnement tillader', 'error');
            return;
        }

        // Alt validering foretaget
        // Tilføj reservation

        $reservationDuration = (int) $userProduct['metadata']['ReservationHours']['value'];

        $this->RCMS->execute('CALL addReservation(?, ?, ?)', array('iii', $userID, $toolID, $reservationDuration));

        $this->RCMS->addLog(LogTypes::ADD_RESERVATION_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::setNotification('Success', 'Værktøjet er nu reserveret til dig');
    }

    /**
     * Sletter en bruger via POST request
     * Tjekker om brugeren stadig har aktive udlejninger
     * Hvis brugeren har et abonnement, opsiges det
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function deleteUser(): void {
        $userIDPost = (int) $_POST['userID'];

        if ($userIDPost !== $this->RCMS->Login->getUserID() && !$this->RCMS->Login->isAdmin()) {
            return;
        }

        // Tjek om brugeren har nogen aktive check ins
        if ($this->getCheckInCountForUser($userIDPost) !== 0) {
            Functions::setNotification('Fejl', 'Brugeren har stadig aktive udlejninger', 'error');
            return;
        }

        $this->cancelSubscription();

        $this->RCMS->execute('CALL removeUser(?)', array('i', $userIDPost));

        $this->RCMS->addLog(LogTypes::DELETE_USER_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::setNotification('Success', 'Brugeren blev slettet');

        // Log ud hvis det er brugeren selv der sletter kontoen
        if ($userIDPost === $this->RCMS->Login->getUserID()) {
            $this->RCMS->Login->log_out();
        } else {
            Functions::redirect('/dashboard');
        }
    }

    /**
     * Gemmer navnet på abonnementet som brugeren har
     * @param $subName
     */
    private function setSubName($subName): void {
        $userID = $this->RCMS->Login->getUserID();

        $this->RCMS->execute('UPDATE Users SET SubName = ? WHERE UserID = ?', array('si', $subName, $userID));

        $_SESSION['user']['SubName'] = $subName;
    }

    /**
     * Sender en API request til Stripe, og annullerer et abonnement
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function cancelSubscription(): void {
        // Tjek om brugeren har nogen aktive check ins
        if ($this->getCheckInCountForUser($this->RCMS->Login->getUserID()) !== 0) {
            Functions::setNotification('Fejl', 'Brugeren har stadig aktive udlejninger', 'error');
            return;
        }

        $customerID = $this->RCMS->Login->getStripeID();
        $subscriptionID = $this->RCMS->StripeWrapper->getSubscriptionID($customerID);

        $this->RCMS->StripeWrapper->getStripeClient()->subscriptions->cancel($subscriptionID);

        $this->setSubName('');

        $this->RCMS->addLog(LogTypes::CANCEL_SUBSCRIPTION_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::setNotification('Success', 'Abonnementet er blevet opsagt');
    }

    /**
     * Sender en API request til Stripe, og opgraderer eller nedgraderer et abonnement
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function upgradeDowngradeSubscription(): void {
        $priceID = $_POST['price_id'];
        $productName = $_POST['product_name'];

        $subscription = $this->RCMS->StripeWrapper->getSubscription('cus_HUiDUdDo6sHbRV');
        $subscriptionID = $subscription->id;
        $client = $this->RCMS->StripeWrapper->getStripeClient();

        $client->subscriptions->update($subscriptionID, [
            'cancel_at_period_end' => false,
            'proration_behavior' => 'create_prorations',
            'items' => [
                [
                    'id' => $subscription->items->data[0]->id,
                    'price' => $priceID,
                ],
            ],
        ]);

        $this->setSubName($productName);

        $logType = $productName === 'Basis' ? LogTypes::DOWNGRADE_SUBSCRIPTION_TYPE_ID : LogTypes::UPGRADE_SUBSCRIPTION_TYPE_ID;
        $this->RCMS->addLog($logType, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::setNotification('Success', 'Dit abonnement er blevet ændret og gemt!');
    }

    /**
     * Sender en API request til Stripe, og opretter et abonnement for en eksisterende kunde
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function newSubscription(): void {
        $customerID = $this->RCMS->Login->getStripeID();
        $priceID = $_POST['priceID'];
        $paymentMethodID = $_POST['paymentMethodID'];

        $client = $this->RCMS->StripeWrapper->getStripeClient();

        try {
            $payment_method = $client->paymentMethods->retrieve(
                $paymentMethodID
            );

            $payment_method->attach([
                'customer' => $customerID
            ]);
        } catch (Exception $e) {
            Functions::outputAJAXResult(400, ['message' => $e->getMessage(), 'data' => [$customerID, $priceID, $paymentMethodID]]);
        }

        // Sæt standard betalingsmetode for kunden
        $client->customers->update($customerID, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodID
            ]
        ]);

        // Opret abonnementet
        $subscription = $client->subscriptions->create([
            'customer' => $customerID,
            'items' => [
                [
                    'price' => $priceID,
                ],
            ],
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        Functions::setNotification('Success', 'Du er nu abonneret!❤');

        $this->setSubName($_POST['product_name']);

        $this->RCMS->addLog(LogTypes::NEW_SUBSCRIPTION_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::outputAJAXResult(200, ['subscription' => $subscription]);
    }

    /**
     * Returnerer true hvis begge arrays, $a og $b, er ens, ellers false.
     * @param array $a
     * @param array $b
     * @return bool
     */
    public function array_equal(array $a, array $b): bool {
        return (
            is_array($a)
            && is_array($b)
            && count($a) === count($b)
            && array_diff($a, $b) === array_diff($b, $a)
        );
    }

    /**
     * Tilføj et Check-In af et værktøj på en bruger, via POST request
     * @return void
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function checkIn(): void {
        $userID = $this->RCMS->Login->getUserID();

        $response = ['result' => ''];
        $barcode = $_POST['tool_barcode'] ?? null;

        if ($this->RCMS->Login->isLoggedIn() === false) {
            $response['result'] = 'Du er ikke logget ind';
            Functions::outputAJAXResult(200, $response);
        }

        if (is_string($barcode) === false || strlen($barcode) !== 13) {
            $response['result'] = 'Stregkode er ikke 13 karakterer';
            Functions::outputAJAXResult(200, $response);
        }

        $tool = $this->getToolByBarcode($barcode);
        if (empty($tool)) {
            $response['result'] = 'Intet værktøj fundet med den stregkode';
            Functions::outputAJAXResult(200, $response);
        }

        $toolID = $tool['ToolID'];
        if ($this->isToolCheckedIn($toolID) || $this->isToolReserved($toolID, $userID)) {
            $response['result'] = 'Værktøjet er allerede udlånt eller reserveret';
            Functions::outputAJAXResult(200, $response);
        }

        $userProduct = $this->getUserProduct($userID);
        if ($userProduct === false) {
            $response['result'] = 'Du har ikke noget abonnement';
            Functions::outputAJAXResult(200, $response);
        }

        if ($this->hasUserReachedMaxCheckouts($userID)) {
            $response['result'] = 'Du har allerede udlånt det antal værktøj som dit abonnement tillader';
            Functions::outputAJAXResult(200, $response);
        }

        // Alt validering foretaget
        // Tilføj checkin

        $checkInDuration = (int) $userProduct['metadata']['MaxCheckoutDays']['value'];

        $this->RCMS->execute('CALL addCheckIn(?, ?, ?)', array('iii', $userID, $toolID, $checkInDuration));

        $this->RCMS->addLog(LogTypes::CHECK_IN_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        $response['result'] = 'success';
        Functions::outputAJAXResult(200, $response);
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
     * Tjekker om et værktøj er reserveret
     *
     * Returnerer false hvis det er brugeren selv, $userID, som har ejer reservationen
     * @param int $toolID
     * @param int $userID
     * @return bool
     */
    public function isToolReserved(int $toolID, int $userID): bool {
        $res = $this->RCMS->execute('SELECT fn_isToolReserved(?, ?) AS isToolReserved', array('ii', $toolID, $userID));
        return (bool) $res->fetch_object()->isToolReserved;
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
     * Returnerer hvor mange reservationer brugeren har
     * @param int $userID
     * @return int
     */
    public function getReservationCountForUser(int $userID): int {
        $res = $this->RCMS->execute('SELECT fn_getReservationCountForUser(?) AS count', array('i', $userID));
        return (int) $res->fetch_object()->count;
    }

    /**
     * Returnerer brugerens abonnement
     * @param int $userID
     * @return array|bool
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getUserProduct(int $userID) {
        $userProduct = null;
        $user = $this->getUserByID($userID);

        if ($user['StripeID']) {
            $productID = $this->RCMS->StripeWrapper->getProductIDForCustomer($user['StripeID']);

            if ($productID) {
                $userProduct = $this->RCMS->StripeWrapper->getStripeProduct($productID);

                if (!empty($userProduct)) {
                    return $userProduct;
                }

                return false;
            }
        }

        return false;
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

        $userProduct = $this->getUserProduct($userID);

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
     * Tjekker om en bruger har reserveret det antal værktøj som deres abonnement tillader
     * @param int $userID
     * @return bool
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function hasUserReachedMaxReservations(int $userID = 0): bool {
        if ($userID === 0) {
            $userID = $this->RCMS->Login->getUserID();
        }

        $userProduct = $this->getUserProduct($userID);

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
     * Henter et værktøj ud fra databasen hvor værktøjets stregkode er lig $barcode
     * @param string $barcode
     * @return array|null
     */
    public function getToolByBarcode(string $barcode): ?array {
        $res = $this->RCMS->execute('CALL getToolByBarcode(?)', array('s', $barcode));
        return $res->fetch_assoc();
    }

    /**
     * Henter en bruger ud fra databasen
     * @param int $userID ID på den bruger som skal hentes ud
     * @return array|null
     */
    public function getUserByID(int $userID): ?array {
        $res = $this->RCMS->execute('CALL getUserByID(?)', array('i', $userID));
        return $res->fetch_assoc();
    }

    /**
     * Redigerer en bruger via en POST request
     * Bruges både når almindelige brugere ændre deres profil, og når personale ændre på andre brugeres profil
     * @return void
     */
    private function editUser(): void {
        if (!is_numeric($_POST['user_id'])) {
            return;
        }

        $userID = (int) $_POST['user_id'];

        if (!$this->authorizeUser($userID)) {
            return;
        }

        //TODO: Tilføj et ekstra felt, "confirm password" og tjek at de er ens

        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $zipcode = $_POST['zipcode'];
        $city = $_POST['city'];
        $level = $this->RCMS->Login::STANDARD_USER_LEVEL;

        if ($this->RCMS->Login->isAdmin()) {
            $level = $_POST['level'];
        }

        $currentUser = $this->getUserByID($userID);

        // Tjek om brugeren vil ændre sin e-mail, og om e-mailen er optaget
        if ($currentUser['Email'] !== $email) {
            $exists = $this->RCMS->execute('CALL getUserByEmail(?)', array('s', $email));
            if ($exists->num_rows !== 0) {
                // E-mail er allerede taget
                Functions::redirect("?userid=$userID&emailtaken");
                return;
            }
        }

        // Tjek om brugeren vil ændre sit password
        if (isset($_POST['password']) && $_POST['password'] !== '') {
            $password = $this->RCMS->Login->hashPass($_POST['password']);
        } else {
            $password = $currentUser['Password'];
        }

        $this->RCMS->execute('CALL editUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array('issssssssi', $userID, $firstname, $lastname, $email, $password, $phone, $address, $zipcode, $city, $level));

        if ($userID === $this->RCMS->Login->getUserID()) {
            // Brugerens information har ændret sig, så de skal opdateres i sessionen
            $user = $this->getUserByID($userID);
            unset($user['Password']);
            $_SESSION['user'] = $user;
        }

        $this->editCustomerInStripe($currentUser['StripeID'], $firstname, $lastname, $email, $phone, $address, $zipcode, $city);

        Functions::setNotification('Gemt', 'Dine ændringer blev gemt');

        Functions::redirect('/dashboard');
    }

    /**
     * Wrapper metode til at redigere en brugers oplysninger i Stripe
     * @param string $stripeCustomerID
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param string $phone
     * @param string $address
     * @param string $zipcode
     * @param string $city
     */
    private function editCustomerInStripe(string $stripeCustomerID, string $firstname, string $lastname, string $email, string $phone, string $address, string $zipcode, string $city): void {
        $params = [
            'name' => $firstname . ' ' . $lastname,
            'email' => $email,
            'phone' => $phone,
            'address' => [
                'line1' => $address,
                'city' => $city,
                'postal_code' => $zipcode,
                'country' => 'DK'
            ],
            'shipping' => [
                'address' => [
                    'line1' => $address,
                    'city' => $city,
                    'postal_code' => $zipcode
                ],
                'name' => $firstname . ' ' . $lastname,
                'phone' => '45' . $phone
            ]
        ];

        $this->RCMS->StripeWrapper->editCustomer($stripeCustomerID, $params);
    }

    /**
     * Fjerner alle kategorier fra et værktøj
     * @param int $toolID
     * @return void
     */
    private function removeAllCategoriesFromTool(int $toolID): void {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $this->RCMS->execute('CALL removeAllCategoriesFromTool(?)', array('i', $toolID));
    }

    /**
     * Returnerer false, hvis $userID ikke er det samme som brugerens ID i databasen og brugeren ikke er personale.
     *
     * Personale kan ændre på alle brugere, så $userID må i de tilfælde godt være et andet ID end det som står i databasen.
     *
     * Metoden returnerer altid false hvis brugeren ikke er logget ind
     * @param int $userID
     * @return bool
     */
    public function authorizeUser(int $userID): bool {
        if (!$this->RCMS->Login->isLoggedIn()) {
            return false;
        }

        if ($this->RCMS->Login->isAdmin() === false && $userID !== $this->RCMS->Login->getUserID()) {
            return false;
        }

        return true;
    }

    /**
     * Tilføjer en producent via en POST request
     * @return void
     */
    private function addManufacturer(): void {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $manufacturerName = $_POST['manufacturer_name'];

        $this->RCMS->execute('CALL addManufacturer(?)', array('s', $manufacturerName));

        $this->RCMS->addLog(LogTypes::CREATE_MANUFACTURER_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::setNotification('Oprettet', 'Producenten blev oprettet');

        Functions::redirect('/dashboard');
    }

    /**
     * Returnerer en producent
     * @param int $manufacturerID
     * @return array|null
     */
    public function getManufacturer(int $manufacturerID): ?array {
        $res = $this->RCMS->execute('CALL getManufacturer(?)', array('i', $manufacturerID));
        return $res->fetch_assoc();
    }

    /**
     * Redigerer en producent via en POST request
     * @return void
     */
    private function editManufacturer(): void {
        if (!is_numeric($_POST['manufacturer_id']) || !$this->RCMS->Login->isAdmin()) {
            return;
        }

        $manufacturerID = (int) $_POST['manufacturer_id'];
        $manufacturerName = $_POST['manufacturer_name'];

        $this->RCMS->execute('CALL editManufacturer(?, ?)', array('is', $manufacturerID, $manufacturerName));

        $this->RCMS->addLog(LogTypes::EDIT_MANUFACTURER_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::setNotification('Gemt', 'Dine ændringer blev gemt');

        Functions::redirect('/dashboard');
    }

    /**
     * Tilføjer en kategori via en POST request
     * @return void
     */
    private function addCategory(): void {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $categoryName = $_POST['category_name'];

        $this->RCMS->execute('CALL addCategory(?)', array('s', $categoryName));

        $this->RCMS->addLog(LogTypes::CREATE_CATEGORY_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::setNotification('Oprettet', 'Kategorien blev oprettet');

        Functions::redirect('/dashboard');
    }

    /**
     * Henter en kategori ud fra databasen
     * @param int $categoryID
     * @return array|null
     */
    public function getCategory(int $categoryID): ?array {
        $res = $this->RCMS->execute('CALL getCategory(?)', array('i', $categoryID));
        return $res->fetch_assoc();
    }

    /**
     * Redigerer en kategori via en POST request
     * @return void
     */
    private function editCategory(): void {
        if (!is_numeric($_POST['category_id']) || !$this->RCMS->Login->isAdmin() ) {
            return;
        }

        $categoryID = (int) $_POST['category_id'];
        $categoryName = $_POST['category_name'];

        $this->RCMS->execute('CALL editCategory(?, ?)', array('is', $categoryID, $categoryName));

        $this->RCMS->addLog(LogTypes::EDIT_CATEGORY_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::setNotification('Gemt', 'Dine ændringer blev gemt');

        Functions::redirect('/dashboard');
    }

    /**
     * Redigerer et værktøj via en POST request
     * @return void
     * @throws Exception
     */
    private function editTool(): void {
        if (!is_numeric($_POST['tool_id']) || !$this->RCMS->Login->isAdmin() ) {
            return;
        }

        $toolID = (int) $_POST['tool_id'];
        $toolName = $_POST['tool_name'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $manufacturerID = $_POST['manufacturer_id'];
        $categories = $_POST['categories'] ?? [];

        $currentTool = $this->getToolByID($toolID);

        // Tjek om kategorier skal opdateres
        if (!empty($categories)) {
            $currentToolCategoryIDs = array_map(static fn($category) => strval($category['CategoryID']), $currentTool['Categories']);

            if ($this->array_equal($categories, $currentToolCategoryIDs) === false) {
                // Opdater kategorier
                $this->removeAllCategoriesFromTool($toolID);
                foreach ($categories as $categoryID) {
                    if (!is_numeric($categoryID)) {
                        continue;
                    }
                    $this->addToolToCategory($toolID, (int) $categoryID);
                }
            }
        }

        // Tjek om billedet skal opdateres
        $imageName = $_FILES['image']['name'] ?? false;
        if ($imageName) {
            // Opdater billede
            $newImageName = $this->uploadImage($imageName, $_FILES['image']['tmp_name']);
            if (!$newImageName) {
                return;
            }
        } else {
            // Behold nuværende billede
            $newImageName = $currentTool['Image'];
        }

        $this->RCMS->execute('CALL editTool(?, ?, ?, ?, ?, ?)', array('issisi', $manufacturerID, $toolName, $description, $status, $newImageName, $toolID));

        $this->RCMS->addLog(LogTypes::EDIT_TOOL_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::setNotification('Gemt', 'Dine ændringer blev gemt');

        Functions::redirect('/dashboard');
    }

    /**
     * Uploader et billede for et værktøj via en POST request, bruger $_FILES array
     * @param string $imageName
     * @param string $tmpName
     * @return bool|string
     * @throws Exception
     */
    private function uploadImage(string $imageName, string $tmpName) {
        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
        $newImageName = date('dmYHis') . '_' . bin2hex(random_bytes(2)) . '.' . $ext;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $tmpName);

        if (!isset($type) || !in_array($type, array("image/png", "image/jpeg", "image/gif"))) {
            $_SESSION['tool_image_upload_error'] = 'Billedet kunne ikke uploades';
            return false;
        }

        $finalImagePath = $this->TOOL_IMAGE_FOLDER . '/' . $newImageName;

        $uploadResult = move_uploaded_file($_FILES['image']['tmp_name'], $finalImagePath);
        if (!$uploadResult) {
            $_SESSION['tool_image_upload_error'] = 'Billedet kunne ikke uploades';
            return false;
        }

        return $newImageName;
    }

    /**
     * Tilføjer et værktøj til databasen via en POST request
     * @return void
     * @throws Exception
     * @return void
     */
    private function addTool(): void {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $toolName = $_POST['tool_name'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $manufacturerID = $_POST['manufacturer_id'];
        $categories = $_POST['categories'] ?? [];

        $imageName = $_FILES['image']['name'] ?? false;
        if (!$imageName) {
            return;
        }

        $newImageName = $this->uploadImage($imageName, $_FILES['image']['tmp_name']);

        if (!$newImageName) {
            return;
        }

        $res = $this->RCMS->execute('CALL addTool(?, ?, ?, ?, ?)', array('issis', $manufacturerID, $toolName, $description, $status, $newImageName));

        $toolID = $res->fetch_assoc()['lastInsertId'];

        foreach ($categories as $categoryID) {
            if (!is_numeric($categoryID)) {
                continue;
            }
            $this->addToolToCategory($toolID, (int) $categoryID);
        }

        $this->RCMS->addLog(LogTypes::CREATE_TOOL_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::setNotification('Oprettet', 'Værktøjet blev oprettet');

        Functions::redirect('/dashboard');
    }

    /**
     * Tilføjer et værktøj til en kategori
     * @param int $toolID
     * @param int $categoryID
     * @return void
     */
    private function addToolToCategory(int $toolID, int $categoryID): void {
        $this->RCMS->execute('CALL addToolToCategory(?, ?)', array('ii', $toolID, $categoryID));
    }

    /**
     * Henter et værktøj ud fra databasen
     * @param int $toolID
     * @return array|false
     */
    public function getToolByID(int $toolID) {
        $res = $this->RCMS->execute('CALL getToolByID(?)', array('i', $toolID));
        $tool = $res->fetch_assoc();

        $tool['Categories'] = $this->getCategoriesForTool($tool['ToolID']);

        if ($tool === null) {
            return false;
        }

        return $tool;
    }

    /**
     * Henter et værktøj ud af databasen via stregkoden, og udskriver resultatet i JSON.
     * Til brug ved AJAX requests.
     */
    private function getToolByBarcodeAjax(): void {
        if (!isset($_POST['tool_barcode']) || strlen($_POST['tool_barcode']) !== 13) {
            Functions::outputAJAXResult(400, ['result' => 'Stregkode er forkert']);
        }

        // TODO: Indsæt hash i session og databasen med bruger ID, tjek efterfølgende på det i checkIn() metoden

        $tool = $this->getToolByBarcode($_POST['tool_barcode']);

        $tool['Image'] = $this->RELATIVE_TOOL_IMAGE_FOLDER . '/' . $tool['Image'];

        $result = [
            'result' => 'success',
            'tool' => $tool
        ];

        $this->RCMS->addLog(LogTypes::SCAN_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Functions::outputAJAXResult(200, $result);
    }

    /**
     * Henter alle vores butikker ud af databasen
     * @return array|mixed
     */
    public function getAllStores() {
        return $this->RCMS->execute('CALL getAllStores()')->fetch_all(MYSQLI_ASSOC) ?? [];
    }

    /**
     * Returnerer et array med lokationer for vores butikker
     * @return array
     */
    public function getStoreLocations(): array {
        $stores = $this->getAllStores();

        $locations = [];

        foreach ($stores as $store) {
            $locations[] = ['lat' => $store['Latitude'], 'long' => $store['Longitude']];
        }

        return $locations;
    }

    /**
     * Henter alle værktøj ud fra databasen
     * @return array
     */
    public function getAllTools(): array {
        $res = $this->RCMS->execute('CALL getAllTools();');

        $tools = $res->fetch_all(MYSQLI_ASSOC);

        foreach ($tools as $key => $tool) {
            $tools[$key]['Categories'] = $this->getCategoriesForTool($tool['ToolID']);
        }

        return $tools ?? [];
    }

    /**
     * Returnerer mængden af værktøj der er i databasen (med filtre), til brug ved pagination
     * @return int
     */
    public function getToolCountWithFilters(): int {
        $filters = $this->getPaginationFilters();

        return (int) $this->RCMS->execute('SELECT fn_GetToolCountBySearch(?, ?, ?) as toolCount', array('ssi', $filters['search-text'], $filters['categories'], $filters['only_in_stock']))->fetch_object()->toolCount;
    }

    /**
     * Udskriver links til forsiden så man kan skifte side og se flere værktøj
     */
    public function displayFrontPagePagination(): void {
        $rowCount = $this->getToolCountWithFilters();
        $pages = ceil($rowCount / self::TOOLS_PER_PAGE);

        $query = $this->getFilterQueryString();

        echo '<span class="page-pagination">';
        for ($i = 1; $i <= $pages; $i++) {
            if ((isset($_GET['pagenum']) && (int) $_GET['pagenum'] === $i) || (!isset($_GET['pagenum']) && $i === 1)) {
                $href = '?pagenum=' . $i . '&' . $query;
                echo "<a class='pageSel' href='{$href}'>{$i}</a>";
            } else {
                $href = '?pagenum=' . $i . '&' . $query;
                echo "<a class='pageNorm' href='{$href}'>{$i}</a>";
            }
        }
        echo '</span>';
    }

    /**
     * Udskriver beskeden på forsiden hvor der står "Viser 1 - x af x på side x"
     */
    public function displayToolCountMessage(): void {
        $page = $_GET['pagenum'] ?? 1;
        $totalToolCount = $this->getToolCountWithFilters();

        $upper = min($totalToolCount, $page * self::TOOLS_PER_PAGE);
        $lower = ($page - 1) * self::TOOLS_PER_PAGE + 1;
        $msg = sprintf( "Viser %d - %d af %d på side %d\n", $lower, $upper, $totalToolCount, $page);
        echo "<p style='margin-top: 0' class='grey-text right'>$msg</p>";
    }

    /**
     * Bygger og formaterer de URL søge parametre vi bruger til at filtrere på forsiden
     * Bruges også til paginering, så man kan skifte side og se flere værktøj
     * Den string man får tilbage kunne f.eks. være "&search-text=test&only_in_stock=1"
     * @return string
     */
    private function getFilterQueryString(): string {
        $vars = explode('&', $_SERVER['QUERY_STRING']);

        $final = array();

        if (!empty($vars)) {
            foreach($vars as $var) {
                if (empty($var)) {
                    continue;
                }

                $parts = explode('=', $var);

                $key = $parts[0];
                $val = $parts[1];

                if (!array_key_exists($key, $final) && $key !== 'pagenum') {
                    $final[$key] = $val;
                }

            }
        }

        return http_build_query($final);
    }

    /**
     * Returnerer de forskellige filtre der kan bruges til søgning af værktøj
     * @return array
     */
    private function getPaginationFilters(): array {
        return [
            'search-text' => isset($_GET['search-text']) ? $_GET['search-text'] : '',
            'categories' => isset($_GET['categories']) ? implode(',', array_map(static fn($category) => (int)$category, $_GET['categories'])) : '',
            'only_in_stock' => isset($_GET['only_in_stock']) ? (int)$_GET['only_in_stock'] : 1,
            'pagenum' => isset($_GET['pagenum']) ? (int)$_GET['pagenum'] : 1
        ];
    }

    /**
     * Henter værktøj ud af databasen, med mulighed for at filtrere på kategorier og søgetekst
     * @return array
     */
    public function getAllToolsWithFilters(): array {
        $filters = $this->getPaginationFilters();

        $res = $this->RCMS->execute('CALL getToolsBySearch(?, ?, ?, ?)', array('ssii', $filters['search-text'], $filters['categories'], $filters['only_in_stock'], $filters['pagenum']));
        $tools = $res->fetch_all(MYSQLI_ASSOC);

        foreach ($tools as $key => $tool) {
            $tools[$key]['Categories'] = $this->getCategoriesForTool($tool['ToolID']);
        }

        return $tools ?? [];
    }

    /**
     * Henter alle statusser ud fra databasen
     * @return array
     */
    public function getAllStatuses(): array {
        $res = $this->RCMS->execute('CALL getAllStatuses();');

        return $res->fetch_all(MYSQLI_ASSOC) ?? [];
    }

    /**
     * Henter alle kategorier ud fra databasen
     * @return array
     */
    public function getAllCategories(): array {
        $res = $this->RCMS->execute('CALL getAllCategories()');

        return $res->fetch_all(MYSQLI_ASSOC) ?? [];
    }

    /**
     * Henter alle kategorier ud for et værktøj fra databasen
     * @param int $toolID
     * @return array
     */
    public function getCategoriesForTool(int $toolID): array {
        $res = $this->RCMS->execute('CALL getCategoriesForTool(?)', array('i', $toolID));

        return $res->fetch_all(MYSQLI_ASSOC) ?? [];
    }

    /**
     * Henter alle producenter ud fra databasen
     * @return array
     */
    public function getAllManufacturers(): array {
        $res = $this->RCMS->execute('CALL getAllManufacturers()');
        return $res->fetch_all(MYSQLI_ASSOC) ?? [];
    }

    /**
     * Henter en bruger ud af databasen via deres e-mail
     * @param string $email
     * @return bool|array
     */
    public function getUserByEmail(string $email) {
        $res = $this->RCMS->execute('CALL getUserByEmail(?)', array('s', $email));

        return $res->fetch_array(MYSQLI_ASSOC) ?? false;
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