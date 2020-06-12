<?PHP

/**
 * Instantiere alle de klasser som ligger i plugins mappen, så loadPlugins() funktionen kan tilføje dem til $GLOBALS
 * @param string $path Stien til plugins mappen
 */
function recursive_require_plugins($path) {
    $dir = new DirectoryIterator($path);
    foreach ($dir as $fileinfo) {
        if ($fileinfo->isDir() && !$fileinfo->isDot()) {
            recursive_require_plugins($fileinfo->getPath() . '/' . $fileinfo->getFilename() . '/');
        } else if (!$fileinfo->isDot() && $fileinfo->getExtension() == 'php') {
            require_once $fileinfo->getPath() . '/' . $fileinfo->getFilename();
        }
    }
}

recursive_require_plugins(__DIR__ . '/plugins/');

require_once(__DIR__ . "/Template.php");
require_once(__DIR__ . "/Functions.php");
require_once(__DIR__ . "/StripeWrapper.php");
require_once(__DIR__ . "/Login.php");

class RCMS {
    /**
     * domæne eller IP til databasen
     * @var string $host
     */
    private $host;

    /**
     * Brugernavn til databasen
     * @var string $user
     */
    private $user;

    /**
     * Adgangskode til databasen
     * @var string $pass
     */
    private $pass;

    /**
     * Navnet på databasen
     * @var string $database
     */
    private $database;

    /**
     * @var mysqli $mysqli
     */
    private $mysqli;

    /**
     * @var Functions $Functions
     */
    public $Functions;

    /**
     * @var Template $Template
     */
    public $Template;

    /**
     * @var Login $Login
     */
    public $Login;

    /**
     * @var StripeWrapper $StripeWrapper
     */
    public $StripeWrapper;

    private $homefolder;
    private $templatefolder;
    private $uploadsfolder;
    private $relativeUploadsFolder;
    private $salt;

    function __construct($host, $user, $pass, $database, $homefolder, $templatefolder, $uploadsfolder, $salt) {
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
     */
    function loadPlugins($path) {
        $dir = new DirectoryIterator($path);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                $this->loadPlugins($fileinfo->getPath() . '/' . $fileinfo->getFilename() . '/');
            } else if (!$fileinfo->isDot() && $fileinfo->getExtension() == 'php') {
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
     */
    function newGlobal($newGlobal, $value) {
        $GLOBALS[$newGlobal] = $value;
    }

    /**
     * Returnerer den absolutte sti til uploads mappen, ex. /home2/virtusbc/tectool.virtusb.com/public_html/include/../uploads/tools/images
     * @return string
     */
    public function getUploadsFolder() {
        return $this->uploadsfolder;
    }

    /**
     * Returnerer den relative sti til uploads mappen, ex. /uploads/tools/images
     * @return string
     */
    public function getRelativeUploadsFolder() {
        return $this->relativeUploadsFolder;
    }

    /**
     * Returnere stien til hjemme/root mappen
     * F.eks. "/"
     * @return string
     */
    function getHomefolder() {
        return $this->homefolder;
    }

    /**
     * Returnere stien til template mappen
     * F.eks. "/template/tectools"
     * @return string
     */
    public function getTemplateFolder() {
        return $this->templatefolder;
    }

    /**
     * Returnere det salt som bruges til adgangskoder for at øge sikkerheden
     * F.eks. hvis salt er "secretsalt" og brugeren ved oprettelse skriver "12356" som adgangskode, bliver deres adgangskode gemt som "secretsalt123456" i databasen
     * @return string
     */
    function getSalt() {
        return $this->salt;
    }

    /**
     * Returnere den oprettede MySQL forbindelse
     * @return mysqli
     */
    function getMySQLI() {
        return $this->mysqli;
    }

    /**
     * Opretter forbindelse til MySQL databasen
     */
    public function connect() {
        $conn = mysqli_connect($this->host, $this->user, $this->pass, $this->database) or die("MySQLi Error!");
        mysqli_set_charset($conn, "utf8");
        $this->mysqli = $conn; 
    }

    /**
     * Eksekvere en MySQL query og bruger prepared statements for at undgå SQL injection
     * @param string $query En MySQL query, f.eks. "SELECT * FROM Users"
     * @param null|array $parameters Et array af typer og parametre, f.eks. ['ssi', &$username, &$firstname, &$userID] - første element er en string over typer (s for string, i for int), efterfølgende elementer er variabler givet med reference (& symbolet betyder reference pass-by-reference)
     * @return false|mysqli_result
     */
    public function execute($query, $parameters = NULL) {
        $stmt = mysqli_prepare($this->mysqli, $query) or die("MySQLi Query Error: " . mysqli_error($this->mysqli));

        if ($parameters != NULL && $parameters != "" && !empty($parameters)) {
            $rc = call_user_func_array(array($stmt, "bind_param"), $parameters);
            $rc = $stmt->execute();

            if (false === $rc) {
                die('bind_param() failed: ' . htmlspecialchars($stmt->error));
            }
        } else {
            $rc = $stmt->execute();
        }

        if (substr($query, 0, 6) === "SELECT" || substr($query, 0, 4) === 'CALL') {
            $result = $stmt->get_result();

            return $result;
        }
        return false;
    }

    /**
     * Henter den side som brugeren gerne vil se fra 'pages' tabellen i databasen
     * @return array|null
     */
    public function getRequestedPage() {
        $request_url = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
        $request_url2 = $request_url . "/";
        if ($request_url === "/index.php" || $request_url === "/index.php/")
            $request_url = "/";

        $result = $this->execute("CALL getRequestedPage(?, ?)", array('ss', &$request_url, &$request_url2));

        $row = NULL;
        if ($result->num_rows > 0)
            $row = $result->fetch_assoc();

        return $row;
    }

    /**
     * Gør så man kan skrive QMARK i en URL i stedet for et spørgsmålstegn
     *
     * Eksempel:
     *
     * $buttons = array(
     *      array(
     *          "button" => '<input type="button" class="btn rbooking-btn" onclick="location.pathname = \'/admin/edituserQMARKuser_id=?\'" value="Rediger bruger" />',
     *          "value" => "id"
     *      )
     * );
     */
    public static function fixURLQueryQuestionMarks() {
        $uri = $_SERVER['REQUEST_URI'];
        $questionMarksReplaced = str_replace('QMARK', '?', $uri);

        if ($uri !== $questionMarksReplaced) {
            header("Location: $questionMarksReplaced");
            exit(0);
        }
    }

}
