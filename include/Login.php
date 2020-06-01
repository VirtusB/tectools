<?PHP
class Login {
    /**
     * @var $RCMS RCMS
     */
	public $RCMS;

	const STANDARD_USER_LEVEL = 1;
	const MIN_LEVEL_FOR_ADMIN = 9;
	
	function __construct($RCMS) {
		$this->RCMS = $RCMS;

        if (isset($_POST['create_new_user']) && $_POST['create_new_user'] == "1") {
            $this->createUser();
        }
	}
	
	public function isLoggedIn() {
		return (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == 1) ? true : false;
	}
	
	public function log_in() {
		$username = $_POST['username'];
		$password = $this->saltPass($_POST['password']);
		
		if ($password == "")
			return;
		
		$result = $this->RCMS->execute("SELECT id, user, level, data FROM users WHERE user = ? AND pass = ? LIMIT 1", array('ss', &$username, &$password));
		if ($result->num_rows == 1){
			$_SESSION['logged_in'] = 1;
			$_SESSION['user'] = $result->fetch_assoc();

			$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
			header('Location: ' . $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		}else{
			header("Location: ?error=1");
		}
	}

	public function createUser() {
	    $username = $_POST['username'];
	    $pass = $_POST['password'];
	    $level = 1;

        $exists = $this->RCMS->execute('SELECT * FROM users WHERE user = ? LIMIT 1', array('s', &$username));

        if ($exists->num_rows !== 0) {
            header('Location: /register/?emailtaken');
            return false;
        }

        $hashedPass = $this->saltPass($pass);

        $this->RCMS->execute('INSERT INTO users (user, pass, level) VALUES (?, ?, ?)', array('sss', &$username, &$hashedPass, &$level));
	}

	public function isAdmin() {
	    return $this->getUserLevel() >= $this::MIN_LEVEL_FOR_ADMIN;
    }

	public function getUsername() {
	    return $_SESSION['user']['user'] ?? false;
    }

    public function getUserID() {
	    return $_SESSION['user']['id'] ?? false;
    }

    public function getUserLevel() {
	    return $_SESSION['user']['level'] ?? false;
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
	
	public function userExists($userID) {
		$result = $this->RCMS->execute("SELECT * FROM users WHERE id = ? LIMIT 1", array('i', &$userID));
		if ($result->num_rows == 1){
			return true;
		}else{
			return false;
		}
	}
	
	public function getUsers($where = ""){
		if ($where == ""){
			$query = "SELECT * FROM users";
		}else{
			$query = "SELECT * FROM users $where";
		}

		if ($result = $this->RCMS->execute($query)){
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
?>