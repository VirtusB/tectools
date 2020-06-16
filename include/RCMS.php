<?php

declare(strict_types=1);

/**
 * Instantiere alle de klasser som ligger i plugins mappen, så loadPlugins() funktionen kan tilføje dem til $GLOBALS
 * @param string $path Stien til plugins mappen
 * @return void
 */
function recursive_require_plugins(string $path) {
    $dir = new DirectoryIterator($path);

    foreach ($dir as $fileinfo) {
        if ($fileinfo->isDir() && !$fileinfo->isDot()) {
            recursive_require_plugins($fileinfo->getPath() . '/' . $fileinfo->getFilename() . '/');
        } else if (!$fileinfo->isDot() && $fileinfo->getExtension() === 'php') {
            require_once $fileinfo->getPath() . '/' . $fileinfo->getFilename();
        }
    }
}

require __DIR__ . '/vendor/autoload.php';

require_once(__DIR__ . "/Template.php");
require_once(__DIR__ . "/Functions.php");
require_once(__DIR__ . "/StripeWrapper.php");
require_once(__DIR__ . "/Login.php");

recursive_require_plugins(__DIR__ . '/plugins/');

class RCMS {
    /**
     * Domæne eller IP til databasen
     * @var string $host
     */
    private string $host;

    /**
     * Brugernavn til databasen
     * @var string $user
     */
    private string $user;

    /**
     * Adgangskode til databasen
     * @var string $pass
     */
    private string $pass;

    /**
     * Navnet på databasen
     * @var string $database
     */
    private string $database;

    /**
     * Forbindelse til databasen
     * @var mysqli $mysqli
     */
    private mysqli $mysqli;

    /**
     * @var Functions $Functions
     */
    public Functions $Functions;

    /**
     * @var Template $Template
     */
    public Template $Template;

    /**
     * @var Login $Login
     */
    public Login $Login;

    /**
     * @var StripeWrapper $StripeWrapper
     */
    public StripeWrapper $StripeWrapper;

    private string $homefolder;
    private string $templatefolder;
    private string $uploadsfolder;
    private string $relativeUploadsFolder;
    private string $salt;

    public function __construct(string $host, string $user, string $pass, string $database, string $homefolder, string $templatefolder, string $uploadsfolder, string $salt) {
        session_start();

        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->database = $database;

        $this->homefolder = $homefolder;
        $this->templatefolder = $homefolder . 'template/' . $templatefolder;
        $this->uploadsfolder = __DIR__ . '/../' . $uploadsfolder;
        $this->relativeUploadsFolder = '/' . $uploadsfolder;


        $this->salt = $salt;

        $this->connect();

        $this->Functions = new Functions($this);
        $this->StripeWrapper = new StripeWrapper($this);
        $this->Login = new Login($this);
        $this->Template = new Template($this);

        $this->loadPlugins(__DIR__ . '/plugins/');

        ob_start();

        require_once 'template/' . $templatefolder . '/index.php';
    }

    /**
     * Tilføjer alle klasser der ligger i plugins mappen til $GLOBALS, så de kan bruges alle steder i koden
     * @param string $path Stien til plugins mappen
     * @return void
     */
    private function loadPlugins(string $path): void {
        $dir = new DirectoryIterator($path);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $this->loadPlugins($fileinfo->getPath() . '/' . $fileinfo->getFilename() . '/');
            } else if (!$fileinfo->isDot() && $fileinfo->getExtension() === 'php') {
                $classname = $fileinfo->getBasename('.php');
                if (class_exists($classname)) {
                    $this->newGlobal($classname, new $classname($this));
                }
            }
        }
    }

    /**
     * Opretter en variabel i $GLOBALS arrayet, $GLOBALS er et indbygget array i PHP som er tilgængeligt alle steder i koden
     * @param string $newGlobal Navnet/key på det nye element
     * @param object $value Et objekt/klasse
     * @return void
     */
    private function newGlobal(string $newGlobal, object $value): void {
        $GLOBALS[$newGlobal] = $value;
    }

    /**
     * Returnerer den absolutte sti til uploads mappen, ex. /home2/virtusbc/tectool.virtusb.com/public_html/include/../uploads/tools/images
     * @return string
     */
    public function getUploadsFolder(): string {
        return $this->uploadsfolder;
    }

    /**
     * Returnerer den relative sti til uploads mappen, ex. /uploads/tools/images
     * @return string
     */
    public function getRelativeUploadsFolder(): string {
        return $this->relativeUploadsFolder;
    }

    /**
     * Returnere stien til hjemme/root mappen
     *
     * F.eks. "/"
     * @return string
     */
    public function getHomeFolder(): string {
        return $this->homefolder;
    }

    /**
     * Returnere stien til template mappen
     *
     * F.eks. "/template/tectools"
     * @return string
     */
    public function getTemplateFolder(): string {
        return $this->templatefolder;
    }

    /**
     * Returnere det salt som bruges til adgangskoder for at øge sikkerheden
     *
     * F.eks. hvis salt er "secretsalt" og brugeren ved oprettelse skriver "12356" som adgangskode, bliver deres adgangskode gemt som "secretsalt123456" i databasen
     * @return string
     */
    public function getSalt(): string {
        return $this->salt;
    }

    /**
     * Returnere den oprettede MySQL forbindelse
     * @return mysqli
     */
    public function getMySQLI(): \mysqli {
        return $this->mysqli;
    }

    /**
     * Opretter forbindelse til MySQL databasen
     * @return void
     */
    public function connect(): void {
        $conn = mysqli_connect($this->host, $this->user, $this->pass, $this->database) or die("MySQLi Error!");
        mysqli_set_charset($conn, "utf8");
        $this->mysqli = $conn; 
    }

    /**
     * Eksekvere en MySQL query og bruger prepared statements for at undgå SQL injection
     * @param string $query En MySQL query, f.eks. "SELECT * FROM Users"
     * @param null|array $parameters Et array af typer og parametre, f.eks. ['ssi', &$username, &$firstname, &$userID] - første element er en string over typer (s for string, i for int), efterfølgende elementer er variabler givet med reference (& symbolet betyder reference pass-by-reference)
     * @return mysqli_result|void
     */
    public function execute(string $query, array $parameters = null) {
        $query = str_ireplace(array("\r","\n",'\r','\n'),'', trim($query));

        $stmt = mysqli_prepare($this->mysqli, $query) or die("MySQLi Query Error: " . mysqli_error($this->mysqli));

        if ($parameters !== null && $parameters !== "" && !empty($parameters)) {
            //$rc = call_user_func_array(array($stmt, "bind_param"), $parameters);

            $types = $parameters[0];
            unset($parameters[0]);

            $rc = $stmt->bind_param($types, ...$parameters);
            $stmt->execute();

            if (false === $rc) {
                die('bind_param() failed: ' . htmlspecialchars($stmt->error));
            }
        } else {
            $stmt->execute();
        }

        if (substr($query, 0, 6) === "SELECT" || substr($query, 0, 4) === 'CALL') {
            return $stmt->get_result();
        }
    }

    /**
     * Henter den side som brugeren gerne vil se fra 'pages' tabellen i databasen
     * @return array|null
     */
    public function getRequestedPage(): ?array {
        $request_url = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
        $request_url2 = $request_url . "/";
        if ($request_url === "/index.php" || $request_url === "/index.php/") {
            $request_url = "/";
        }

        $result = $this->execute("CALL getRequestedPage(?, ?)", array('ss', &$request_url, &$request_url2));

        $row = null;
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        }

        return $row;
    }

    /**
     * Gør så man kan skrive QMARK i en URL i stedet for et spørgsmålstegn
     *
     * Eksempel:
     *
     * $buttons = array(
     *      array(
     *          "button" => '<input type="button" class="btn rbooking-btn" onclick="location.pathname = \'/admin/edituserQMARKuserid=?\'" value="Rediger bruger" />',
     *          "value" => "id"
     *      )
     * );
     * @return void
     */
    public static function fixURLQueryQuestionMarks(): void {
        $uri = $_SERVER['REQUEST_URI'];
        $questionMarksReplaced = str_replace('QMARK', '?', $uri);

        if ($uri !== $questionMarksReplaced) {
            header("Location: $questionMarksReplaced");
            exit(0);
        }
    }

}
