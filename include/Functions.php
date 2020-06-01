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

    function countDigits( $str )
    {
        return preg_match_all( "/[0-9]/", $str );
    }

	function isAdminPage(){
		$request = $this->RCMS->getRequestedPage();
		return ($request['is_admin_page'] && $this->RCMS->Login->isLoggedIn()) ? true : false;
	}

	function formatPrice($price) {
	    return number_format(floatval($price), 2, ',', '.');
    }

    function super_unique($array,$key)
    {
        $temp_array = [];
        foreach ($array as &$v) {
            if (!isset($temp_array[$v[$key]]))
                $temp_array[$v[$key]] =& $v;
        }
        $array = array_values($temp_array);
        return $array;
    }

    function array_last($array) {
        if (empty($array)) {
            return null;
        }
        foreach (array_slice($array, -1) as $value) {
            return $value;
        }
    }

    function getPrevKey($key, $hash = array())
    {
        $keys = array_keys($hash);
        $found_index = array_search($key, $keys);
        if ($found_index === false || $found_index === 0)
            return false;
        return $keys[$found_index-1];
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
