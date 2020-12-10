<?php

declare(strict_types=1);

require_once 'Categories.php';
require_once 'Manufacturers.php';
require_once 'Reservations.php';
require_once 'CheckIns.php';
require_once 'Subscriptions.php';
require_once 'Users.php';

/**
 * Class TecTools
 * Denne klasse indeholder metoder som vedrører værktøj og butikker, samt meget andet, på TecTools siden
 * Den indeholder metoder til bl.a. oprette, redigere og hente værktøj
 * Klassen står også for at loade vores andre klasser som er nødvendige for at siden fungere
 */
class TecTools {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    /**
     * @var Categories $Categories
     */
    public Categories $Categories;

    /**
     * @var Manufacturers $Manufacturers
     */
    public Manufacturers $Manufacturers;

    /**
     * @var Reservations $Reservations
     */
    public Reservations $Reservations;

    /**
     * @var CheckIns $CheckIns
     */
    public CheckIns $CheckIns;

    /**
     * @var Subscriptions $Subscriptions
     */
    public Subscriptions $Subscriptions;

    /**
     * @var Users $Users
     */
    public Users $Users;

    /**
     * Absolutte sti til mappen hvor billeder af værktøj ligger
     * @var string $TOOL_IMAGE_FOLDER
     */
    public string $TOOL_IMAGE_FOLDER;

    /**
     * Relative sti til mappen hvor billeder af værktøj ligger
     * @var string $RELATIVE_TOOL_IMAGE_FOLDER
     */
    public string $RELATIVE_TOOL_IMAGE_FOLDER;

    /**
     * Antal værktøj der bliver vist på forsiden per side
     */
    private const TOOLS_PER_PAGE = 8;

    /**
     * Status værdi for værktøj som er på lager
     */
    public const TOOL_AVAILABLE_STATUS = 1;

    /**
     * Status værdi for værktøj som er reserveret
     */
    public const TOOL_RESERVED_STATUS = 2;

    /**
     * Status værdi for værktøj som er udlånt
     * @var int TOOL_AVAILABLE_STATUS
     */
    public const TOOL_LOANED_OUT_STATUS = 3;

    /**
     * Status værdi for værktøj som ikke er på lager (ex. demo vare, udgået, udsolgt)
     * @var int TOOL_AVAILABLE_STATUS
     */
    public const TOOL_NOT_IN_STOCK_STATUS = 4;

    /**
     * Status værdi for værktøj som er beskadiget
     * @var int TOOL_AVAILABLE_STATUS
     */
    public const TOOL_DAMAGED_STATUS = 5;

    /**
     * Liste over POST endpoints (metoder), som kan eksekveres automatisk
     * Vi er nød til at have en liste over tilladte endpoints, så brugere ikke kan eksekvere alle metoder i denne klasse
     * @var $allowedEndpoints array|string[]
     */
    public static array $allowedEndpoints = [
        'addTool', 'editTool', 'getToolByBarcodeAjax', 'contactCustomerService'
    ];

    /**
     * Array over klasser som indeholder metoder som skal kunne kaldes via POST requests
     * @var array $POSTClasses
     */
    public array $POSTClasses = [];

    public function __construct(RCMS $RCMS) {
        $this->RCMS = $RCMS;
        $this->TOOL_IMAGE_FOLDER = $this->RCMS->getUploadsFolder() . '/tools/images';
        $this->RELATIVE_TOOL_IMAGE_FOLDER = $this->RCMS->getRelativeUploadsFolder() . '/tools/images';

        $this->POSTClasses[] = $this;

        $this->Categories = new Categories($this);
        $this->Manufacturers = new Manufacturers($this);
        $this->Reservations = new Reservations($this);
        $this->CheckIns = new CheckIns($this);
        $this->Subscriptions = new Subscriptions($this);
        $this->Users = new Users($this);

        $this->handlePOSTEndpoints();
    }

    /**
     * Henter et værktøj ud fra databasen hvor værktøjets stregkode er lig $barcode
     * @param string $barcode
     * @return array|null
     */
    public function getToolByBarcode(string $barcode): ?array {
        return $this->RCMS->execute('CALL getToolByBarcode(?)', array('s', $barcode))->fetch_assoc() ?? null;
    }

    /**
     * Redigerer et værktøj via en POST request
     * @return void
     * @throws Exception
     */
    public function editTool(): void {
        if (!is_numeric($_POST['tool_id']) || !$this->RCMS->Login->isAdmin() ) {
            return;
        }

        $toolID = (int) $_POST['tool_id'];
        $toolName = $_POST['tool_name'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $manufacturerID = $_POST['manufacturer_id'];
        $categories = $_POST['categories'] ?? [];

        $currentTool = $this->getToolByID($toolID);

        // Tjek om kategorier skal opdateres
        if (!empty($categories)) {
            $currentToolCategoryIDs = array_map(static fn($category) => strval($category['CategoryID']), $currentTool['Categories']);

            if (Helpers::array_equal($categories, $currentToolCategoryIDs) === false) {
                // Opdater kategorier
                $this->Categories->removeAllCategoriesFromTool($toolID);
                foreach ($categories as $categoryID) {
                    if (!is_numeric($categoryID)) {
                        continue;
                    }
                    $this->Categories->addToolToCategory($toolID, (int) $categoryID);
                }
            }
        }

        // Tjek om billedet skal opdateres
        $imageName = $_FILES['image']['name'] ?? false;
        if ($imageName) {
            // Opdater billede
            $newImageName = $this->uploadImage($imageName, $_FILES['image']['tmp_name']);
            if (!$newImageName) {
                return;
            }
        } else {
            // Behold nuværende billede
            $newImageName = $currentTool['Image'];
        }

        $this->RCMS->execute('CALL editTool(?, ?, ?, ?, ?, ?)', array('issisi', $manufacturerID, $toolName, $description, $status, $newImageName, $toolID));

        $this->RCMS->Logs->addLog(Logs::EDIT_TOOL_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Gemt', 'Dine ændringer blev gemt');

        Helpers::redirect('/dashboard');
    }

    /**
     * Uploader et billede for et værktøj via en POST request, bruger $_FILES array
     * @param string $imageName
     * @param string $tmpName
     * @return string|false
     * @throws Exception
     */
    private function uploadImage(string $imageName, string $tmpName) {
        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
        $newImageName = date('dmYHis') . '_' . bin2hex(random_bytes(2)) . '.' . $ext;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $tmpName);

        if (!isset($type) || !in_array($type, array("image/png", "image/jpeg", "image/gif"))) {
            $_SESSION['tool_image_upload_error'] = 'Billedet kunne ikke uploades';
            return false;
        }

        $finalImagePath = $this->TOOL_IMAGE_FOLDER . '/' . $newImageName;

        $uploadResult = move_uploaded_file($_FILES['image']['tmp_name'], $finalImagePath);
        if (!$uploadResult) {
            $_SESSION['tool_image_upload_error'] = 'Billedet kunne ikke uploades';
            return false;
        }

        return $newImageName;
    }

    /**
     * Tilføjer et værktøj til databasen via en POST request
     * @return void
     * @throws Exception
     * @return void
     */
    public function addTool(): void {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $toolName = $_POST['tool_name'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $manufacturerID = $_POST['manufacturer_id'];
        $categories = $_POST['categories'] ?? [];

        $imageName = $_FILES['image']['name'] ?? false;
        if (!$imageName) {
            return;
        }

        $newImageName = $this->uploadImage($imageName, $_FILES['image']['tmp_name']);

        if (!$newImageName) {
            return;
        }

        $res = $this->RCMS->execute('CALL addTool(?, ?, ?, ?, ?)', array('issis', $manufacturerID, $toolName, $description, $status, $newImageName));

        $toolID = $res->fetch_assoc()['lastInsertId'];

        foreach ($categories as $categoryID) {
            if (!is_numeric($categoryID)) {
                continue;
            }
            $this->Categories->addToolToCategory($toolID, (int) $categoryID);
        }

        $this->RCMS->Logs->addLog(Logs::CREATE_TOOL_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Oprettet', 'Værktøjet blev oprettet');

        Helpers::redirect('/dashboard');
    }

    /**
     * Henter et værktøj ud fra databasen
     * @param int $toolID
     * @return array|false
     */
    public function getToolByID(int $toolID) {
        $tool = $this->RCMS->execute('CALL getToolByID(?)', array('i', $toolID))->fetch_assoc() ?? null;

        if ($tool === null) {
            return false;
        }

        $tool['Categories'] = $this->Categories->getCategoriesForTool($tool['ToolID']);

        return $tool;
    }

    /**
     * Sørger for at stien til billedet er korrekt, således at det kan udskrives på siden
     * @param $path
     * @return string
     */
    public function cleanImagePath(string $path): string {
        return $this->RELATIVE_TOOL_IMAGE_FOLDER . '/' . $path;
    }

    /**
     * Henter et værktøj ud af databasen via stregkoden, og udskriver resultatet i JSON.
     * Via POST request
     */
    public function getToolByBarcodeAjax(): void {
        if (!isset($_POST['tool_barcode']) || strlen($_POST['tool_barcode']) !== 13) {
            Helpers::outputAJAXResult(400, ['result' => 'Stregkode er forkert']);
        }

        $tool = $this->getToolByBarcode($_POST['tool_barcode']);

        $tool['Image'] = $this->cleanImagePath($tool['Image']);

        $result = [
            'result' => $tool
        ];

        $this->RCMS->Logs->addLog(Logs::SCAN_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::outputAJAXResult(200, $result);
    }

    /**
     * Henter alle vores butikker ud af databasen
     * @return array
     */
    public function getAllStores(): array {
        return $this->RCMS->execute('CALL getAllStores()')->fetch_all(MYSQLI_ASSOC) ?? [];
    }

    /**
     * Returnerer et array med lokationer for vores butikker
     * @return array
     */
    public function getStoreLocations(): array {
        $stores = $this->getAllStores();

        $locations = [];

        foreach ($stores as $store) {
            $locations[] = ['lat' => $store['Latitude'], 'long' => $store['Longitude']];
        }

        return $locations;
    }

    /**
     * Returnerer antal af værktøj der er i databasen (med filtre), til brug ved pagination
     * @return int
     */
    public function getToolCountWithFilters(): int {
        $filters = $this->getPaginationFilters();

        return (int) $this->RCMS->execute('SELECT fn_GetToolCountBySearch(?, ?, ?) as toolCount', array('ssi', $filters['search-text'], $filters['categories'], $filters['only_in_stock']))->fetch_object()->toolCount;
    }

    /**
     * Udskriver links til Tools siden så man kan skifte side og se flere værktøj
     */
    public function displayToolsPagePagination(): void {
        $rowCount = $this->getToolCountWithFilters();
        $pages = ceil($rowCount / self::TOOLS_PER_PAGE);

        $query = $this->getFilterQueryString();
        //var_dump($query);

        echo '<span class="page-pagination">';
        for ($i = 1; $i <= $pages; $i++) {
            if ((isset($_GET['pagenum']) && (int) $_GET['pagenum'] === $i) || (!isset($_GET['pagenum']) && $i === 1)) {
                $href = '?pagenum=' . $i . '&' . $query;
                echo "<a class='pageSel' href='{$href}'>{$i}</a>";
            } else {
                $href = '?pagenum=' . $i . '&' . $query;
                echo "<a class='pageNorm' href='{$href}'>{$i}</a>";
            }
        }
        echo '</span>';
    }

    /**
     * Udskriver beskeden på Tools siden hvor der står "Viser 1 - x af x på side x"
     */
    public function displayToolCountMessage(): void {
        $page = $_GET['pagenum'] ?? 1;
        $totalToolCount = $this->getToolCountWithFilters();

        $upper = min($totalToolCount, $page * self::TOOLS_PER_PAGE);
        $lower = ($page - 1) * self::TOOLS_PER_PAGE + 1;
        $msg = sprintf( "Viser %d - %d af %d på side %d\n", $lower, $upper, $totalToolCount, $page);
        echo "<p style='margin-top: 0' class='grey-text right'>$msg</p>";
    }

    /**
     * Bygger og formaterer de URL søge parametre vi bruger til at filtrere på forsiden
     * Bruges også til paginering, så man kan skifte side og se flere værktøj
     * Den string man får tilbage kunne f.eks. være "&search-text=test&only_in_stock=1"
     * @return string
     */
    private function getFilterQueryString(): string {
        $queryString = urldecode($_SERVER['QUERY_STRING']);

        $q = parse_url($queryString);
        $qs = [];
        parse_str($q['path'], $qs);

        foreach ($qs as $key => $var) {
            if ($key === 'pagenum') {
                unset($qs[$key]);
            }
        }

        return urldecode(http_build_query($qs));
    }

    /**
     * Returnerer de forskellige filtre der kan bruges til søgning af værktøj
     * @return array
     */
    private function getPaginationFilters(): array {
        return [
            'search-text' => isset($_GET['search-text']) ? $_GET['search-text'] : '',
            'categories' => isset($_GET['categories']) ? implode(',', array_map(static fn($category) => (int)$category, $_GET['categories'])) : '',
            'only_in_stock' => isset($_GET['only_in_stock']) ? (int)$_GET['only_in_stock'] : 1,
            'pagenum' => isset($_GET['pagenum']) ? (int)$_GET['pagenum'] : 1
        ];
    }

    /**
     * Henter værktøj ud af databasen og filtrere på kategorier og søgetekst hvis det er valgt
     * @return array
     */
    public function getAllToolsWithFilters(): array {
        $filters = $this->getPaginationFilters();

        //echo '<pre>';
        //print_r($filters);
        //echo '</pre>';

        $res = $this->RCMS->execute('CALL getToolsBySearch(?, ?, ?, ?)', array('ssii', $filters['search-text'], $filters['categories'], $filters['only_in_stock'], $filters['pagenum']));

        $tools = $res->fetch_all(MYSQLI_ASSOC);

        foreach ($tools as $key => $tool) {
            $tools[$key]['Categories'] = $this->Categories->getCategoriesForTool($tool['ToolID']);
        }

        return $tools ?? [];
    }

    /**
     * Henter alle statusser ud fra databasen
     * @return array
     */
    public function getAllStatuses(): array {
        $res = $this->RCMS->execute('CALL getAllStatuses();');

        return $res->fetch_all(MYSQLI_ASSOC) ?? [];
    }

    /**
     * Denne metode tjekker, om $_POST arrayet indeholder navnet på en metode i denne klasse eller de andre TecTools underklasser,
     * tjekker derefter om det er en af de tilladte endpoints,
     * og eksekvere efterfølgende metoden hvis det er tilfældet
     *
     * Det er endda muligt for handlePOSTEndpoints at overdrage funktionsparametre til målfunktionen
     */
    public function handlePOSTEndpoints(): void {
        if (!isset($_POST['post_endpoint'])) {
            return;
        }

        $endpoint = $_POST['post_endpoint'];
        $POSTClass = null;

        foreach ($this->POSTClasses as $class) {
            if (method_exists($class, $endpoint)) {
                $POSTClass = $class;
            }
        }

        if ($POSTClass === null) {
            Helpers::setNotification('Fejl', 'Ukendt POST endpoint', 'error');
            return;
        }

        $endpointAllowed = null;

        if (!in_array($endpoint, $POSTClass::$allowedEndpoints, true)) {
            Helpers::setNotification('Fejl', 'Denne funktion må ikke kaldes via POST', 'error');
            return;
        }

        $args = [];

        $reflectionMethod = new ReflectionMethod($POSTClass, $endpoint);
        $params = $reflectionMethod->getParameters();

        foreach ($params as $param) {
            if (isset($_POST[$param->getName()])) {
                if ($param->getType() !== null) {
                    $typeName = $param->getType()->getName();
                    $value = $_POST[$param->getName()];
                    $castStatement = 'return (' . $typeName . ') ' . $value .';';
                    $args[] = eval($castStatement);
                } else {
                    $args[] = $_POST[$param->getName()];
                }
            }
        }

        if (empty($args)) {
            $POSTClass->$endpoint();
        } else {
            $POSTClass->$endpoint(...$args);
        }
    }

    /**
     * Sender en mail til TecTools personalet
     * En kunde kan kontakte TecTools personalet via kontakt siden
     */
    private function contactCustomerService(): void {
        $generatedCaptcha = $_SESSION['contact_page_captcha'];
        $userSuppliedCaptcha = $_POST['verification'];

        if ((int) $generatedCaptcha !== (int) $userSuppliedCaptcha) {
            Helpers::setNotification('Fejl', 'Regnestykket er forkert', 'error');
            return;
        }

        $message = $_POST['message'];
        $customerName = $_POST['firstname'] . ' '. $_POST['lastname'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];

        $body = <<<HTML
        <p>En kunde har sendt en besked via kontakt siden</p>
        <br>
        <p>Kunde navn: $customerName</p>
        <p>Kunde telefonnummer: $phone</p>
        <p>Kunde e-mail adresse: $email</p>
        <p>Kundens besked:</p>
        <i>$message</i>
        <br>
        <br>
        <p>Med venlig hilsen TecTools</p>
        <img style="max-height: 53px" src="cid:TTLogo" alt="Logo" />
HTML;

        $logoPath = __DIR__ . '/../../../' . $this->RCMS->getTemplateFolder() . '/images/logo.png';

        Mailer::sendEmail(
            SMTP_USERNAME,
            'TecTools',
            SMTP_USERNAME,
            'TecTools',
            'TecTools - kundebesked',
            $body, [], [], 'TTLogo', $logoPath);

        Helpers::redirect('?sent');
    }
}