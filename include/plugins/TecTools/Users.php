<?php

declare(strict_types=1);

/**
 * Class Users
 * Denne klasse indeholder metoder som vedrører brugere på TecTools siden
 * Den indeholder metoder til bl.a. redigere og slette brugere, og til at hente brugerens abonnement
 */
class Users {
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
        'editUser', 'deleteUser'
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
     * Returnerer brugerens Stripe Customer ID
     * @return bool|string
     */
    public function getStripeID() {
        return $_SESSION['user']['StripeID'] ?? false;
    }

    /**
     * Sletter en bruger via POST request
     * Tjekker om brugeren stadig har aktive udlejninger
     * Hvis brugeren har et abonnement, opsiges det
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function deleteUser(): void {
        $userIDPost = (int) $_POST['userID'];

        if ($userIDPost !== $this->RCMS->Login->getUserID() && !$this->RCMS->Login->isAdmin()) {
            return;
        }

        // Tjek om brugeren har nogen aktive check ins
        if ($this->TecTools->CheckIns->getCheckInCountForUser($userIDPost) !== 0) {
            Helpers::setNotification('Fejl', 'Brugeren har stadig aktive udlejninger', 'error');
            return;
        }

        $this->TecTools->Subscriptions->cancelSubscription();

        $this->RCMS->StripeWrapper->removeCustomer($this->getStripeID());

        $this->RCMS->execute('CALL removeUser(?)', array('i', $userIDPost));

        $this->RCMS->Logs->addLog(Logs::DELETE_USER_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Succes', 'Brugeren blev slettet');

        // Log ud hvis det er brugeren selv der sletter kontoen
        if ($userIDPost === $this->RCMS->Login->getUserID()) {
            $this->RCMS->Login->log_out();
        } else {
            Helpers::redirect('/dashboard');
        }
    }

    /**
     * Returnerer brugerens abonnement
     * @param int $userID
     * @return array|false
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
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function editUser(): void {
        if (!is_numeric($_POST['user_id'])) {
            return;
        }

        $userID = (int) $_POST['user_id'];

        if (!$this->authorizeUser($userID)) {
            return;
        }

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
            $user = $this->RCMS->Login->getUserByEmail($email);
            if ($user) {
                // E-mail er allerede taget
                Helpers::redirect("?userid=$userID&emailtaken");
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

        Helpers::setNotification('Gemt', 'Dine ændringer blev gemt');

        Helpers::redirect('/dashboard');
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
     * @throws \Stripe\Exception\ApiErrorException
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
}