<?php

declare(strict_types=1);

use Stripe\Exception\ApiErrorException;

class Login {
    /**
     * @var RCMS $RCMS
     */
	public RCMS $RCMS;

	public const STANDARD_USER_LEVEL = 1;
	public const MIN_LEVEL_FOR_ADMIN = 9;
	
	public function __construct(RCMS $RCMS) {
		$this->RCMS = $RCMS;

        if (isset($_POST['log_in']) && $_POST['log_in'] === '1') {
            $this->log_in();
        }

        if (isset($_GET['log_out']) && $_GET['log_out'] === '1') {
            $this->log_out();
        }

        if (isset($_POST['create_new_user']) && $_POST['create_new_user'] === '1') {
            $this->createUser();
        }

        $this->setLoginCookies();
	}

    /**
     * Sætter nogle cookies relateret til RCMS som skal bruges på frontend
     */
    private function setLoginCookies(): void {
        $isAdmin = $this->isAdmin();
        setcookie('RCMS_isAdmin', (string) $isAdmin, time()+3600, '/');
    }

    /**
     * Returnerer true hvis brugeren er logget ind, ellers false
     * @return bool
     */
	public function isLoggedIn(): bool {
		return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === 1;
	}

    /**
     * Logger en bruger ind via en POST request
     * @return void
     */
	public function log_in(): void {
		$email = $_POST['email'];
		$password = $_POST['password'];

		if ($password === "") {
            return;
        }

		$result = $this->RCMS->execute("CALL getUserByEmail(?)", array('s', $email));

		if ($result->num_rows === 1) {
		    $user = $result->fetch_assoc();

		    if (password_verify($password, $user['Password'])) {
                unset($_SESSION['createUserPOST'], $_SESSION['user']['Password']);
                $_SESSION['logged_in'] = 1;
                $_SESSION['user'] = $user;

                $this->RCMS->addLog(LogTypes::LOG_IN_TYPE_ID, ['UserID' => $user['UserID']]);

                Helpers::setNotification('Success', 'Du er nu logget på');
                Helpers::redirect('/dashboard');
            } else {
                Helpers::redirect('/login?wrong_email_or_password');
            }
		} else {
			Helpers::redirect('/login?wrong_email_or_password');
		}
	}

    /**
     * Opretter en bruger via en POST request
     * @return void
     * @throws ApiErrorException
     * @noinspection PhpUndefinedVariableInspection
     */
	public function createUser(): void {
        extract($_POST, EXTR_OVERWRITE);

        $exists = $this->RCMS->execute('CALL getUserByEmail(?)', array('s', $email));

        if ($exists->num_rows !== 0) {
            // E-mail er allerede brugt
            unset($_POST['password'], $_POST['repeat_password']);
            $_SESSION['createUserPOST'] = $_POST;
            Helpers::redirect('?emailtaken');

            return;
        }

        if ($password !== $repeat_password) {
            unset($_POST['password'], $_POST['repeat_password']);
            $_SESSION['createUserPOST'] = $_POST;
            Helpers::redirect('?confirm_password');

            return;
        }

        unset($_SESSION['createUserPOST']);

        $hashedPass = $this->hashPass($password);

        $stripeID = $this->addUserToStripe($firstname, $lastname, $email, $phone, $address, $zipcode, $city);

        $this->RCMS->execute('CALL addUser(?, ?, ?, ?, ?, ?, ?, ?, ?)', array('sssssssss', $firstname, $lastname, $email, $hashedPass, $phone, $address, $zipcode, $city, $stripeID));

        $this->log_in();
	}

    /**
     * Tilføjer en bruger som en customer i Stripe og returnerer det customer ID som Stripe returnerer efter oprettelse
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param string $phone
     * @param string $address
     * @param string $zipcode
     * @param string $city
     * @return string
     * @throws ApiErrorException
     */
    private function addUserToStripe(string $firstname, string $lastname, string $email, string $phone, string $address, string $zipcode, string $city): string {
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

        $customer = $this->RCMS->StripeWrapper->createCustomer($params);

        return $customer->id;
    }

    /**
     * Returnerer true hvis brugeren er personale, ellers false
     * @return bool
     */
	public function isAdmin(): bool {
	    return $this->getUserLevel() >= $this::MIN_LEVEL_FOR_ADMIN;
    }

    /**
     * Returnerer brugerens email
     * @return bool|string
     */
	public function getEmail() {
	    return $_SESSION['user']['Email'] ?? false;
    }

    /**
     * Returnerer brugerens Stripe Customer ID
     * @return bool|string
     */
    public function getStripeID() {
	    return $_SESSION['user']['StripeID'] ?? false;
    }

    /**
     * Returnerer brugerens fornavn
     * @return bool|string
     */
    public function getFirstName() {
        return $_SESSION['user']['FirstName'] ?? false;
    }

    /**
     * Returnerer brugerens efternavn
     * @return bool|string
     */
    public function getLastName() {
        return $_SESSION['user']['LastName'] ?? false;
    }

    /**
     * Returnerer brugerens abonnement navn
     * @return bool|string
     */
    public function getSubName() {
        return $_SESSION['user']['SubName'] ?? false;
    }

    /**
     * Returnerer brugerens ID
     * @return bool|int
     */
    public function getUserID() {
	    return $_SESSION['user']['UserID'] ?? false;
    }

    /**
     * Returnerer 1 hvis brugeren er standard, eller 9 hvis brugeren er personale
     * @return bool|int
     */
    public function getUserLevel() {
	    return $_SESSION['user']['Level'] ?? false;
    }

    /**
     * Logger en bruger ud fra siden ved at slette brugeren fra $_SESSION
     * @param string $customLocation URL som brugeren skal sendes til efter man er blevet logget ud
     * @return void
     */
	public function log_out(string $customLocation = ''): void {
        $this->RCMS->addLog(LogTypes::LOG_OUT_TYPE_ID, ['UserID' => $this->getUserID()]);

        unset($_SESSION['logged_in'], $_SESSION['user']);
        if ($customLocation !== '') {
            header($customLocation);
        } else {
            header("Location: /");
        }
	}

    /**
     * Salter brugerens adgangskode og krypterer med MD5
     * @param string $pass Brugerens adgangskode
     * @return string
     */
	public function hashPass(string $pass): string {
		return password_hash($pass, PASSWORD_DEFAULT);
	}
}
