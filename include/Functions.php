<?PHP
class Functions {
	var $RCMS;
	
	function __construct($RCMS) {
		$this->RCMS = $RCMS;
	}
	
	//Use this to check if current page is the front page
	function isFrontPage() {
		$request_url = htmlspecialchars(strip_tags(mysqli_real_escape_string($this->RCMS->getMySQLI(), $_SERVER["REQUEST_URI"])));
		

		if ($request_url == $this->RCMS->getHomefolder() . "index.php" || $request_url == $this->RCMS->getHomefolder() || $request_url == $this->RCMS->getHomefolder() . "index.php/" || (isset($_GET['search-country']) && isset($_GET['search-text']))){
			return true;
		} else {
			return false;
		}
	}

	function isAdminPage(){
		$request = $this->RCMS->getRequestedPage();
		return ($request['is_admin_page'] && $this->RCMS->Login->isLoggedIn()) ? true : false;
	}

    function outputError($error, $htmlTag = 'p', $centerAlign = false) {
        $hideMessageLink = <<<HTML
            <a onclick="$(this.parentElement.parentElement).hide()" style="font-size: 13px; vertical-align: text-top;" href="javascript:void(0)">Skjul</a>
HTML;


        if ($centerAlign) {
            echo '<div style="text-align: center; width: 100%;">';
            echo "<$htmlTag style='color: #e26239'>$error $hideMessageLink</$htmlTag>";
            echo '</div>';
        } else {
            echo '<div>';
            echo "<$htmlTag style='color: #e26239'>$error $hideMessageLink</$htmlTag>";
            echo '</div>';
        }
    }
}
