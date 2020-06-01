<?PHP

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
require_once(__DIR__ . "/Login.php");

class RCMS {
    private $version = '1.0.0';
    private $host;
    private $user;
    private $pass;
    private $database;
    private $mysqli;

    public $Functions;
    public $Template;
    public $Login;

    private $homefolder;
    private $templatefolder;

    private $salt;

    private $cron;

    function __construct($host, $user, $pass, $database, $homefolder, $templatefolder, $uploadsfolder, $salt) {
        session_start();
        $this->cron = NULL;

        if (class_exists('Cron')) {
            $this->cron = new Cron($this);
        }

        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->database = $database;

        $this->homefolder = $homefolder;
        $this->templatefolder = $homefolder . 'template/' . $templatefolder;
        $this->uploadsfolder = __DIR__ . '/../' . $uploadsfolder;

        $this->salt = $salt;

        $this->connect();

        $this->Functions = new Functions($this);
        $this->Login = new Login($this);
        $this->eventhandler();


        $this->Template = new Template($this);

        $this->loadPlugins(__DIR__ . '/plugins/');

        ob_start();

        if ($this->cron != NULL) {
            $this->cron->runCronJobs();
        }

        require_once 'template/' . $templatefolder . '/index.php';
        echo $this->addMetaGeneratorVersion();
    }

    function getVersion() {
        return $this->version;
    }

    function addMetaGeneratorVersion() {
        return "<meta name='generator' content='RCMS {$this->getVersion()}'>";
    }

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

    function getData($table, $where) {
        $rows = false;

        $res = $this->execute("SELECT data FROM $table $where");

        if ($res->num_rows > 0 && $res->num_rows !== 1) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }

        if ($res->num_rows === 1) {
            return $res->fetch_assoc();
        }

        return $rows;
    }

    function newGlobal($newGlobal, $value) {
        $GLOBALS[$newGlobal] = $value;
    }

    function getHomefolder() {
        return $this->homefolder;
    }

    function getTemplateFolder() {
        return $this->templatefolder;
    }

    function getSalt() {
        return $this->salt;
    }

    function getMySQLI() {
        return $this->mysqli;
    }

    function getInsertedId() {
        return $this->getMySQLI()->insert_id;
    }

    function getCron() {
        return $this->cron;
    }

    //Used at build to connect to MySQL
    public function connect() {
        $conn = mysqli_connect($this->host, $this->user, $this->pass, $this->database) or die("MySQLi Error!");
        mysqli_set_charset($conn, "utf8");
        $this->mysqli = $conn;
    }

    //Used for all MySQL executions, for a safer MySQL connection and standard
    public function execute($query, $parameters = NULL) {
        if ($parameters != NULL && $parameters != "" && !empty($parameters)) {
            $stmt = mysqli_prepare($this->mysqli, $query) or die("MySQLi Query Error: " . mysqli_error($this->mysqli));
            $rc = call_user_func_array(array($stmt, "bind_param"), $parameters);
            $rc = $stmt->execute();

            if (false === $rc) {
                die('bind_param() failed: ' . htmlspecialchars($stmt->error));
            }
            if (substr($query, 0, 6) == "SELECT") {
                $result = $stmt->get_result();
                return $result;
            }
        } else {
            $result = mysqli_query($this->getMySQLI(), $query) or die("MySQLi Query Error: " . mysqli_error($this->getMySQLI()));

            if (substr($query, 0, 6) == "SELECT") {
                return $result;
            }
        }
    }

    //Use this for catching events via both POST and/or GET
    private function eventhandler() {
        if (isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'GET')) {
            if (isset($_POST['log_in']) && $_POST['log_in'] == 1) {
                //Return search results via function
                $this->Login->log_in();
            }

            if (isset($_GET['log_out']) && $_GET['log_out'] == 1) {
                //Return search results via function
                $this->Login->log_out();
            }
        }
    }

    //Use this for matching requested page with saved pages in database
    public function getRequestedPage() {
        $request_url = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
        $request_url2 = $request_url . "/";
        if ($request_url == "/index.php" || $request_url == "/index.php/")
            $request_url = "/";

        $result = $this->execute("SELECT * FROM pages WHERE (url = ? OR url = ?) AND active = 1 LIMIT 1", array('ss', &$request_url, &$request_url2));

        $row = NULL;
        if ($result->num_rows > 0)
            $row = $result->fetch_assoc();

        return $row;
    }

    //Use this for test, and general error messages, for troubleshooting later
    function logToFile($file, $text) {
        $target_dir = "logs/";
        $target_file = $target_dir . basename($file);

        $text = "-----\r\n" . date("j-n-Y H:i:s", time()) . " - " . $text . "\r\n";

        file_put_contents($target_file, $text, FILE_APPEND);
    }

    /**
     * Gør så man kan skrive QMARK i en URL i stedet for et spørgsmålstegn
     *
     * Eksempel:
     *
     * $buttons = array(
     *      array(
     *          "button" => '<input type="button" class="btn rbooking-btn" onclick="location.pathname = \'/admin/editticketQMARKticket_id=?\'" value="Rediger" />',
     *          "value" => "id"
     *      )
     * );
     */
    static function fixURLQueryQuestionMarks() {
        $uri = $_SERVER['REQUEST_URI'];
        $questionMarksReplaced = str_replace('QMARK', '?', $uri);

        if ($uri !== $questionMarksReplaced) {
            header("Location: $questionMarksReplaced");
            exit(0);
        }
    }

}
