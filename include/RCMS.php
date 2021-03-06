<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

require_once(__DIR__ . "/Mailer.php");
require_once(__DIR__ . "/Template.php");
require_once(__DIR__ . "/Helpers.php");
require_once(__DIR__ . "/StripeWrapper.php");
require_once(__DIR__ . "/Login.php");
require_once(__DIR__ . "/Logs.php");

/**
 * Class RCMS
 * Denne klasse er entry-pointet for enhver webside der bruger RCMS-systemet.
 * Klassen står for at forbinde til MySQL og for at loade andre nødvendige klasser.
 * Den indeholder "execute" metoden, som står for at eksekvere SQL på en sikker måde, ved at benytte parametre og Prepared Statements
 */
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
     * @var Helpers $Helpers
     */
    public Helpers $Helpers;

    /**
     * @var Template $Template
     */
    public Template $Template;

    /**
     * @var Login $Login
     */
    public Login $Login;

    /**
     * @var Logs $Logs
     */
    public Logs $Logs;

    /**
     * Cron klassen, hvis den eksisterer
     * @var Cron|null $cron
     */
    private ?Cron $Cron = null;

    /**
     * @var StripeWrapper $StripeWrapper
     */
    public StripeWrapper $StripeWrapper;

    /**
     * @var Mailer $Mailer
     */
    public Mailer $Mailer;

    /**
     * Root mappen, som regel "/"
     * @var string $homefolder
     */
    private string $homefolder;

    /**
     * Template mappen
     * For TecTools er den "/template/tectools"
     * @var string $templatefolder
     */
    private string $templatefolder;

    /**
     * Absolutte sti til uploads mappen
     * @var string $uploadsfolder
     */
    private string $uploadsfolder;

    /**
     * Relative sti til uploads mappen
     * @var string $relativeUploadsFolder
     */
    private string $relativeUploadsFolder;

    /**
     * Array af plugin klasser som skal loades
     * @var array
     */
    private array $pluginsToLoad = [];

    public function __construct(string $host, string $user, string $pass, string $database, string $homefolder, string $templatefolder, string $uploadsfolder, string $secretStripeKey, string $environment = '') {
        if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_lifetime' => 1200,
            ]);
        }

        $this->recursive_require_plugins(__DIR__ . '/plugins/');

        if (class_exists('Cron')) {
            $this->Cron = new Cron($this);
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

        $this->Mailer = new Mailer($this);
        $this->Logs = new Logs($this);
        $this->Helpers = new Helpers($this);

        $this->StripeWrapper = new StripeWrapper($this, $secretStripeKey);
        $this->Login = new Login($this);
        $this->Template = new Template($this);

        $this->instantiatePlugins();

        ob_start();

        if ($this->Cron !== null) {
            $this->Cron->runCronJobs();
        }

        require_once 'template/' . $templatefolder . '/index.php';

        if ($environment === '' || $environment === 'production') {
            echo ob_get_clean();
        } else {
            ob_end_clean();
        }
    }

    /**
     * Loader alle de klasser som ligger i plugins mappen, så instantiatePlugins() metoden kan instantiere dem
     * Loader IKKE klasser hvor shouldAutoLoad er false
     * @param string $path Stien til plugins mappen
     * @return void
     */
    private function recursive_require_plugins(string $path): void {
        $dir = new RecursiveDirectoryIterator($path);
        $dir->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);

        $directory = new RecursiveDirectoryIterator($path);
        foreach (new RecursiveIteratorIterator($directory) as $fileInfo) {
            if ($fileInfo->getExtension() === 'php') {
                $this->pluginsToLoad[] = [
                    'path' => $fileInfo->getPath(),
                    'name' => $fileInfo->getFilename(),
                    'basename' => $fileInfo->getBasename('.php'),
                    'shouldAutoLoad' => $this->shouldAutoLoad($fileInfo->getPath() . '/' . $fileInfo->getFilename())
                ];
            }
        }

        usort($this->pluginsToLoad, static function($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        foreach ($this->pluginsToLoad as $key => $plugin) {
            if (!$plugin['shouldAutoLoad']) {
                unset($this->pluginsToLoad[$key]);
                continue;
            }

            $fullPath = $plugin['path'] . '/' . $plugin['name'];

            require_once $fullPath;
        }
    }

    private function shouldAutoLoad($file): bool {
        $contents =  file_get_contents($file);

        return !preg_match('/\bdisableAutoLoading\b/', $contents);
    }

    /**
     * Instantiere alle plugins der ligger i $pluginsToLoad arrayet og tilføjer dem til $GLOBALS arrayet, så den kan bruges alle steder i koden
     * Metoden instantiere IKKE abstrakte klasser
     * @return void
     * @throws ReflectionException
     */
    private function instantiatePlugins(): void {
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
     * @param string $name Navnet/key på det nye element
     * @param object $obj Et objekt/klasse
     * @return void
     */
    private function newGlobal(string $name, object $obj): void {
        $GLOBALS[$name] = $obj;
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
    public function getMySQLi(): mysqli {
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
     * Eksekvere en MySQL query og bruger Prepared Statements for at undgå SQL injection
     * @param string $query En MySQL query, f.eks. "SELECT * FROM Users"
     * @param null|array $parameters Et array af typer og parametre, f.eks. ['ssi', $username, $firstname, $userID] - første element er en string over typer (s for string, i for int), efterfølgende elementer er variabler givet med reference (& symbolet betyder reference pass-by-reference)
     * @return mysqli_result|void
     */
    public function execute(string $query, array $parameters = null) {
        $query = str_ireplace(array("\r","\n",'\r','\n'),'', trim($query));

        $stmt = mysqli_prepare($this->mysqli, $query) or die("MySQLi Query Error: " . mysqli_error($this->mysqli));

        if ($parameters !== null && !empty($parameters)) {
            $types = $parameters[0];
            unset($parameters[0]);

            $rc = $stmt->bind_param($types, ...$parameters);
            $stmt->execute();

            if (false === $rc) {
                die('bind_param() failed: ' . htmlspecialchars($stmt->error, ENT_QUOTES | ENT_HTML5));
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
    public function getCron(): Cron {
        return $this->Cron;
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
}
