<?php

declare(strict_types=1);

class Functions {
	public RCMS $RCMS;
	
	public function __construct(RCMS $RCMS) {
		$this->RCMS = $RCMS;
	}

    /**
     * Returnerer true hvis brugeren er på forsiden, ellers false
     * @return bool
     */
	public function isFrontPage(): bool {
		$request_url = htmlspecialchars(strip_tags(mysqli_real_escape_string($this->RCMS->getMySQLI(), $_SERVER["REQUEST_URI"])));

		if (isset($_GET['search-text']) || $request_url === $this->RCMS->getHomeFolder() . "index.php" || $request_url === $this->RCMS->getHomeFolder() || $request_url === $this->RCMS->getHomeFolder() . "index.php/") {
			return true;
		}

		return false;
	}

	public static function setNotification($title, $message, $type = 'success'): void {
	    $data = [
	        'title' => $title,
	        'message' => $message,
            'type' => $type
        ];

	    $data = json_encode($data);

	    setcookie('notificationFrontend', $data, time()+3600);
    }

    /**
     * Returnerer data til klienten i JSON format
     * @param int $status HTTP status kode, ex. 200 eller 404 osv.
     * @param array $result Array af data som skal udskrives til brugeren
     * @return void
     */
    public function outputAJAXResult(int $status, array $result): void {
        ob_get_clean();
        ob_start();
        header('Content-Type: application/json');

        http_response_code($status);

        echo json_encode($result);

        die();
    }

    /**
     * Fjerner HTML fra en string
     * @param string $str
     * @return string
     */
	public function escape(string $str): string {
        return htmlentities($str, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Laver en ny DateTime ud af $dateTimeString, og returnerer true hvis den DateTime er i fremtiden, ellers false
     * @param string $dateTimeString
     * @return bool
     * @throws Exception
     */
	public static function isFutureDateTimeString(string $dateTimeString): bool {
	    return new DateTime() < new DateTime($dateTimeString);
    }

    /**
     * Returnerer true hvis brugeren er logget ind og siden er en personale side
     * @return bool
     */
	public function isAdminPage(): bool {
		$request = $this->RCMS->getRequestedPage();
		return $request['is_admin_page'] && $this->RCMS->Login->isLoggedIn();
	}

    /**
     * Udskriver en fejl på siden
     * @param string $error Beskeden som brugeren skal have vist
     * @param string $htmlTag Hvilket element der skal udskrives, f.eks. p for paragraph eller h1
     * @param bool $centerAlign Kontrollere om beskeden skal centreres
     * @return void
     */
    public static function outputError(string $error, string $htmlTag = 'p', bool $centerAlign = false): void {
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
