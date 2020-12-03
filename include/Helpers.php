<?php

declare(strict_types=1);

/**
 * Class Helpers
 * Denne klasse indeholder hjælpe funktioner.
 * Bl.a. til at omdirigere brugeren, sætte notifikationer osv.
 */
class Helpers {
	public RCMS $RCMS;
	
	public function __construct(RCMS $RCMS) {
		$this->RCMS = $RCMS;
	}

    /**
     * Returnerer true hvis brugeren er på forsiden, ellers false
     * @return bool
     */
	public function isFrontPage(): bool {
		$request_url = htmlspecialchars(strip_tags(mysqli_real_escape_string($this->RCMS->getMySQLi(), $_SERVER["REQUEST_URI"])), ENT_QUOTES | ENT_HTML5);

        return isset($_GET['search-text']) || $request_url === $this->RCMS->getHomeFolder() . "index.php" || $request_url === $this->RCMS->getHomeFolder() || $request_url === $this->RCMS->getHomeFolder() . "index.php/";
    }

    /**
     * Sætter en cookie i browseren, som javascript i klienten finder og viser notifikationen
     * @param $title
     * @param $message
     * @param string $type
     * @throws JsonException
     */
	public static function setNotification(string $title = '', string $message = '', string $type = 'success'): void {
        $data = json_encode([
            'title' => $title,
            'message' => $message,
            'type' => $type
        ], JSON_THROW_ON_ERROR);

	    setcookie('notificationFrontend', $data, time()+3600, '/');
    }

    /**
     * Genererer et GUID
     * Version 4 på 36 karakterer (med bindestreger)
     * @return string
     * @throws Exception
     */
    public static function guidv4(): string {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Returnerer HTTP host med protokol
     * Ex. https://tectools.virtusb.com
     * @return string
     */
    public static function getHTTPHost(): string {
        return 'https://' . $_SERVER['HTTP_HOST'];
    }

    /**
     * Returnerer true hvis begge arrays, $a og $b, er ens, ellers false.
     * @param array $a
     * @param array $b
     * @return bool
     */
    public static function array_equal(array $a, array $b): bool {
        return (
            count($a) === count($b)
            && array_diff($a, $b) === array_diff($b, $a)
        );
    }

    /**
     * Returnerer data til klienten i JSON format
     * @param int $status HTTP status kode, ex. 200 eller 404 osv.
     * @param array $result
     * @return void
     * @throws JsonException
     */
    public static function outputAJAXResult(int $status, array $result): void {
        ob_end_clean();
        ob_start();
        header('Content-Type: application/json');

        http_response_code($status);

        echo json_encode($result, JSON_THROW_ON_ERROR);

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
     * Omdirigerer brugeren
     * @param $page
     */
	public static function redirect($page): void {
	    if (PHP_SAPI !== 'cli') {
            @header("Location:  $page");
        }
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
