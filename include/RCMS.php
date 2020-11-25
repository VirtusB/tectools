<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

require_once(__DIR__ . "/Template.php");
require_once(__DIR__ . "/Helpers.php");
require_once(__DIR__ . "/StripeWrapper.php");
require_once(__DIR__ . "/Login.php");
require_once(__DIR__ . "/LogTypes.php");

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
     * @var Helpers $Functions
     */
    public Helpers $Functions;

    /**
     * @var Template $Template
     */
    public Template $Template;

    /**
     * @var Login $Login
     */
    public Login $Login;

    /**
     * Cron klassen, hvis den eksisterer
     * @var Cron $cron
     */
    private $cron;

    /**
     * @var StripeWrapper $StripeWrapper
     */
    public StripeWrapper $StripeWrapper;

    private string $homefolder;
    private string $templatefolder;
    private string $uploadsfolder;
    private string $relativeUploadsFolder;

    private array $pluginsToLoad = [];

    public function __construct(string $host, string $user, string $pass, string $database, string $homefolder, string $templatefolder, string $uploadsfolder, string $secretStripeKey, string $environment = '') {
        if (!headers_sent() && session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $this->recursive_require_plugins(__DIR__ . '/plugins/');

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
        $this->relativeUploadsFolder = '/' . $uploadsfolder;

        $this->connect();

        $this->Functions = new Helpers($this);

        $this->StripeWrapper = new StripeWrapper($this, $secretStripeKey);
        $this->Login = new Login($this);
        $this->Template = new Template($this);

        $this->loadPlugins(__DIR__ . '/plugins/');

        ob_start();

        if ($this->cron !== null) {
            $this->cron->runCronJobs();
        }

        require_once 'template/' . $templatefolder . '/index.php';

        if ($environment === '' || $environment === 'production') {
            echo ob_get_clean();
        } else {
            ob_end_clean();
        }
    }

    /**
     * Instantiere alle de klasser som ligger i plugins mappen, så loadPlugins() metoden kan tilføje dem til $GLOBALS
     * @param string $path Stien til plugins mappen
     * @return void
     */
    private function recursive_require_plugins(string $path): void {
        $dir = new RecursiveDirectoryIterator($path);
        $dir->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);

        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->getExtension() === 'php') {
                $this->pluginsToLoad[] = [
                    'path' => $fileInfo->getPath(),
                    'name' => $fileInfo->getFilename(),
                    'basename' => $fileInfo->getBasename('.php')
                ];
            }
        }

        usort($this->pluginsToLoad, static function($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        foreach ($this->pluginsToLoad as $plugin) {
            $fullPath = $plugin['path'] . '/' . $plugin['name'];
            require_once $fullPath;
        }
    }

    /**
     * Tilføjer alle klasser der ligger i plugins mappen til $GLOBALS, så de kan bruges alle steder i koden
     * @param string $path Stien til plugins mappen
     * @return void
     */
    private function loadPlugins(string $path): void {
        //$dir = new DirectoryIterator($path);
        //foreach ($dir as $fileinfo) {
        //    if ($fileinfo->isDir() && !$fileinfo->isDot()) {
        //        $this->loadPlugins($fileinfo->getPath() . '/' . $fileinfo->getFilename() . '/');
        //    } else if (!$fileinfo->isDot() && $fileinfo->getExtension() === 'php') {
        //        $classname = $fileinfo->getBasename('.php');
        //        if (class_exists($classname)) {
        //            $reflectionClass = new ReflectionClass($classname);
        //            if (!$reflectionClass->isAbstract()) {
        //                $this->newGlobal($classname, new $classname($this));
        //            }
        //        }
        //    }
        //}

        foreach ($this->pluginsToLoad as $plugin) {
            $className = $plugin['basename'];

            $reflectionClass = new ReflectionClass($className);
            if (!$reflectionClass->isAbstract()) {
                $this->newGlobal($className, new $className($this));
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
     * @param null|array $parameters Et array af typer og parametre, f.eks. ['ssi', $username, $firstname, $userID] - første element er en string over typer (s for string, i for int), efterfølgende elementer er variabler givet med reference (& symbolet betyder reference pass-by-reference)
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
        $request_url = explode('?', $_SERVER['REQUEST_URI'] ?? '', 2)[0];
        $request_url2 = $request_url . "/";

        if ($request_url === "/index.php" || $request_url === "/index.php/") {
            $request_url = "/";
        }

        $result = $this->execute("CALL getRequestedPage(?, ?)", array('ss', $request_url, $request_url2));

        $row = null;
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        }

        return $row;
    }

    /**
     * Returnerer instansen af Cron klassen
     * @return Cron
     */
    public function getCron(): \Cron {
        return $this->cron;
    }

    /**
     * Lukker for en RCMS instans
     * Bruges til development og testing
     */
    public function closeRCMS(): void {
        $this->mysqli->close();

        $vars = array_keys(get_defined_vars());
        $count = sizeOf($vars);

        for ($i = 0; $i < $count; $i++) {
            unset($$vars[$i]);
        }
        unset($vars,$i);
        $GLOBALS = [];
        $_SESSION = [];
    }

    /**
     * Giver mulighed for at skrive "QMARK" i en URL i stedet for et spørgsmålstegn (?)
     *
     * Nødvendig da RCMSTables erstatter spørgsmålstegn med specifikke værdier, men vi skal også bruge spørgsmålstegn for at betegne URL parametre
     *
     * Eksempel:
     *
     * $buttons = array(
     *      array(
     *          "button" => '<input type="button" class="btn rbooking-btn" onclick="location.pathname = `/admin/users/editQMARKuserid=?`" value="Rediger bruger" />',
     *          "value" => "id"
     *      )
     * );
     * @return void
     */
    public static function fixURLQueryQuestionMarks(): void {
        $uri = $_SERVER['REQUEST_URI'] ?? null;
        if (!$uri) {
            return;
        }

        $questionMarksReplaced = str_replace('QMARK', '?', $uri);

        if ($uri !== $questionMarksReplaced) {
            Helpers::redirect($questionMarksReplaced);
            exit(0);
        }
    }

    public function addLog(int $LogTypeID, array $data) {
        $json = json_encode($data);

        $this->execute('CALL addLog(?, ?)', array('is', $LogTypeID, $json));
    }
}
