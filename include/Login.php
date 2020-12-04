<?php

declare(strict_types=1);

use Stripe\Exception\ApiErrorException;

/**
 * Class Login
 * Denne klasse indeholder metoder der vedrører brugere i RCMS generelt
 * Indeholder bl.a. metoder til at oprette bruger, logge ind og logge ud
 */
class Login {
    /**
     * @var RCMS $RCMS
     */
	public RCMS $RCMS;

	public const STANDARD_USER_LEVEL = 1;
	public const MIN_LEVEL_FOR_ADMIN = 9;
	
	public function __construct(RCMS $RCMS) {
		$this->RCMS = $RCMS;

        if (isset($_POST['log_in'])) {
            $this->log_in();
        }

        if (isset($_GET['log_out'])) {
            $this->log_out();
        }

        if (isset($_POST['create_new_user'])) {
            $this->createUser();
        }

        if (isset($_POST['resetPassword'])) {
            $this->resetPassword();
        }

        if (isset($_POST['resetPasswordVerify'])) {
            $this->resetPasswordVerify();
        }

        $this->setLoginCookies();
	}

    /**
     * Begynder processen til at gendanne et password
     * Opretter et hash i databasen og sender en mail til den person som gerne vil gendanne sit password
     * Via POST request
     * @throws JsonException
     */
	private function resetPassword(): void {
	    $email = $_POST['email'];
	    $user = $this->getUserByEmail($email);

	    if (!$user) {
	        Helpers::setNotification('Fejl', 'Vi kunne ikke finde en bruger med den e-mail adresse', 'error');
	        return;
        }

	    $link = Helpers::guidv4();
        $this->RCMS->execute('CALL addPasswordReset(?, ?)', array('ss', $link, $email));
        $url = Helpers::getHTTPHost() . '/forgot-password?hash=' . $link;

        $name = $user['FirstName'] . ' ' . $user['LastName'];

        $this->sendResetPasswordMail($name, $url, $email);

        Helpers::redirect('?sent');
    }

    /**
     * Sender en mail som indeholder et link hvor brugeren kan vælge en ny adgangskode
     * @param string $fullName
     * @param string $url
     * @param string $emailAddress
     */
    private function sendResetPasswordMail(string $fullName, string $url, string $emailAddress): void {
        $siteName = SITE_NAME;

        $body = <<<HTML
        <p>Kære $fullName</p>
        <p>Vi er meget kede af, at du har mistet adgang til $siteName 💔</p>
        <br>
        <h3>
            <a href="$url">Klik her for at vælge en ny adgangskode</a>
        </h3>
        <br>
        <p>Med venlig hilsen $siteName</p>
        <img style="max-height: 53px" src="cid:TTLogo" alt="Logo" />
HTML;

        $logoPath = __DIR__ . '/..' . $this->RCMS->getTemplateFolder() . '/images/logo.png';

        Mailer::sendEmail(
            SMTP_USERNAME,
            SITE_NAME,
            $emailAddress,
            $fullName,
            "$siteName - gendan adgangskode",
            $body, [], [], 'TTLogo', $logoPath);
    }

    /**
     * Denne metode validere en adgangskode gendannelse
     * Tjekker at linket ikke er brugt og at der ikke er gået 30 minutter, siden linket blev genereret
     * Via POST request
     * @throws JsonException
     */
    private function resetPasswordVerify(): void {
        $hash = $_POST['hash'];
        $password = $_POST['password'];
        $repeat_password = $_POST['repeat_password'];

        if (empty($hash) || strlen($hash) !== 36) {
            Helpers::setNotification('Fejl', 'Linket kan ikke benyttes', 'error');
            return;
        }

        if ($password !== $repeat_password) {
            Helpers::setNotification('Fejl', 'Adgangskoderne er ikke ens', 'error');
            return;
        }

        $reset = $this->getPasswordReset($hash);

        if (!$reset) {
            Helpers::setNotification('Fejl', 'Linket kan ikke benyttes', 'error');
            return;
        }

        if ($reset['IsUsed'] === 1) {
            Helpers::setNotification('Fejl', 'Linket er allerede blevet brugt. Start forfra.', 'error');
            return;
        }

        $currentDateTime = new DateTime();
        $resetRequestDateTime = new DateTime($reset['Created']);
        $resetRequestDateTime = $resetRequestDateTime->add(new DateInterval('P30M')); // Tilføj 30 min

        if ($currentDateTime > $resetRequestDateTime) {
            Helpers::setNotification('Fejl', 'Linket er kun aktivt i 30 minutter. Start forfra.', 'error');
            return;
        }

        $hashedPassword = $this->hashPass($password);
        $email = $reset['Email'];

        $this->RCMS->execute('CALL updatePassword(?, ?)', array('ss', $email, $hashedPassword));
        $this->RCMS->execute('CALL setPasswordResetUsed(?)', array('s', $hash));

        Helpers::setNotification('Succes', 'Din adgangskode blev ændret');

        Helpers::redirect('/login');
    }

    /**
     * Returnerer en adgangskode gendannelse fra databasen, hvis den eksisterer
     * @param string $hash
     * @return array|false
     */
    private function getPasswordReset(string $hash) {
	   return $this->RCMS->execute('CALL getPasswordReset(?)', array('s', $hash))->fetch_assoc() ?? false;
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
     * Henter en bruger ud af databasen via deres email
     * @param string $email
     * @return array|false|null
     */
	public function getUserByEmail(string $email) {
	    return $this->RCMS->execute("CALL getUserByEmail(?)", array('s', $email))->fetch_assoc() ?? false;
    }

    /**
     * Logger en bruger ind via en POST request
     * @return void
     * @throws JsonException
     */
	public function log_in(): void {
		$email = $_POST['email'];
		$password = $_POST['password'];

		if ($password === "") {
            return;
        }

		$user = $this->getUserByEmail($email);

		if ($user !== false) {
		    if (password_verify($password, $user['Password'])) {
                unset($_SESSION['createUserPOST'], $_SESSION['user']['Password']);
                $_SESSION['logged_in'] = 1;
                $_SESSION['user'] = $user;

                $this->RCMS->Logs->addLog(Logs::LOG_IN_TYPE_ID, ['UserID' => $user['UserID']]);

                Helpers::setNotification('Succes', 'Du er nu logget på');
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

        $res = $this->RCMS->execute('CALL addUser(?, ?, ?, ?, ?, ?, ?, ?, ?)', array('sssssssss', $firstname, $lastname, $email, $hashedPass, $phone, $address, $zipcode, $city, $stripeID));
        $userID = $res->fetch_assoc()['lastInsertId'];
        $this->RCMS->Logs->addLog(Logs::CREATE_USER_TYPE_ID, ['UserID' => $userID]);

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
        $this->RCMS->Logs->addLog(Logs::LOG_OUT_TYPE_ID, ['UserID' => $this->getUserID()]);

        unset($_SESSION['logged_in'], $_SESSION['user']);
        if ($customLocation !== '') {
            header($customLocation);
        } else {
            header("Location: /");
        }
	}

    /**
     * Hasher og salter brugerens adgangskode
     * @param string $pass Brugerens adgangskode
     * @return string
     */
	public function hashPass(string $pass): string {
		return password_hash($pass, PASSWORD_DEFAULT);
	}
}
