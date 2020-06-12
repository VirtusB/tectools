<?PHP
class Login {
    /**
     * @var RCMS $RCMS
     */
	public $RCMS;

	const STANDARD_USER_LEVEL = 1;
	const MIN_LEVEL_FOR_ADMIN = 9;
	
	function __construct($RCMS) {
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
	}
	
	public function isLoggedIn() {
		return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === 1;
	}
	
	public function log_in() {
		$email = $_POST['email'];
		$password = $this->saltPass($_POST['password']);
		
		if ($password === "") {
            return;
        }
		
		$result = $this->RCMS->execute("CALL getUserByEmailAndPassword(?, ?)", array('ss', &$email, &$password));
		if ($result->num_rows === 1){
			$_SESSION['logged_in'] = 1;
			$_SESSION['user'] = $result->fetch_assoc();
            unset($_SESSION['createUserPOST'], $_SESSION['user']['password']);

            header('Location: /dashboard');
		} else {
			header("Location: ?error=1");
		}
	}

	public function createUser() {
	    $email = $_POST['email'];
	    $password = $_POST['password'];
	    $firstname = $_POST['firstname'];
	    $lastname = $_POST['lastname'];
	    $phone = $_POST['phone'];
	    $address = $_POST['address'];
	    $city = $_POST['city'];
	    $zipcode = $_POST['zipcode'];

        $exists = $this->RCMS->execute('CALL getUserByEmail(?)', array('s', &$email));

        if ($exists->num_rows !== 0) {
            unset($_POST['password']);
            $_SESSION['createUserPOST'] = $_POST;
            header('Location: /register/?emailtaken');
            return false;
        }
        unset($_SESSION['createUserPOST']);

        $hashedPass = $this->saltPass($password);

        $stripeID = $this->addUserToStripe($firstname, $lastname, $email, $phone, $address, $zipcode, $city);

        $this->RCMS->execute('CALL addUser(?, ?, ?, ?, ?, ?, ?, ?, ?)', array('sssssssss', &$firstname, &$lastname, &$email, &$hashedPass, &$phone, &$address, &$zipcode, &$city, &$stripeID));

        $this->log_in();
	}

    private function addUserToStripe($firstname, $lastname, $email, $phone, $address, $zipcode, $city) {
        $params = [
            'name' => $firstname . ' ' . $lastname,
            'email' => $email,
            'phone' => $phone,
            'address' => [
                'line1' => $address,
                'city' => $city,
                'postal_code' => $zipcode
            ]
        ];

        /**
         * @var \Stripe\Customer $customer
         */
        $customer = $this->RCMS->StripeWrapper->createCustomer($params);

        return $customer->id;
    }

    public function userExists($userID) {
        $result = $this->RCMS->execute("CALL getUserByID(?)", array('i', &$userID));
        if ($result->num_rows === 1){
            return true;
        }

        return false;
    }

	public function isAdmin() {
	    return $this->getUserLevel() >= $this::MIN_LEVEL_FOR_ADMIN;
    }

	public function getEmail() {
	    return $_SESSION['user']['Email'] ?? false;
    }

    public function getStripeID() {
	    return $_SESSION['user']['StripeID'] ?? false;
    }

    public function getFirstName() {
        return $_SESSION['user']['FirstName'];
    }

    public function getUserID() {
	    return $_SESSION['user']['UserID'] ?? false;
    }

    public function getUserLevel() {
	    return $_SESSION['user']['Level'] ?? false;
    }
	
	public function log_out($customLocation = '') {
        unset($_SESSION['logged_in'], $_SESSION['user']);
        if ($customLocation !== '') {
            header($customLocation);
        } else {
            header("Location: /");
        }
	}
	
	public function saltPass($pass) {
		return md5($this->RCMS->getSalt() . $pass . $this->RCMS->getSalt());
	}

	public function getUsers() {
		if ($result = $this->RCMS->execute('CALL getAllUsers()')){
			$rows = array(); 
			while ($row = $result->fetch_assoc()) {
				$rows[] = $row;
			}
		}else{
			echo "Der er ingen brugere, eller der opstod en fejl!";
		}
		
		return $rows;
	}
}
