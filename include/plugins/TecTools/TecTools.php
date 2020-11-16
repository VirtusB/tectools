<?php

declare(strict_types=1);

class TecTools {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    /**
     * Absolut sti til mappen hvor billeder af værktøj ligger
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

    public const TOOL_AVAILABLE_STATUS = 1;
    public const TOOL_RESERVED_STATUS = 2;
    public const TOOL_LOANED_OUT_STATUS = 3;
    public const TOOL_NOT_IN_STOCK_STATUS = 4;

    public function __construct(RCMS $RCMS) {
        $this->RCMS = $RCMS;
        $this->TOOL_IMAGE_FOLDER = $this->RCMS->getUploadsFolder() . '/tools/images';
        $this->RELATIVE_TOOL_IMAGE_FOLDER = $this->RCMS->getRelativeUploadsFolder() . '/tools/images';

        if (isset($_POST['add_tool'])) {
            $this->addTool();
        }

        if (isset($_POST['edit_tool'])) {
            $this->editTool();
        }

        if (isset($_POST['add_category'])) {
            $this->addCategory();
        }

        if (isset($_POST['edit_category'])) {
            $this->editCategory();
        }

        if (isset($_POST['add_manufacturer'])) {
            $this->addManufacturer();
        }

        if (isset($_POST['edit_manufacturer'])) {
            $this->editManufacturer();
        }

        if (isset($_POST['edit_user'])) {
            $this->editUser();
        }

        if (isset($_POST['check_in_tool'])) {
            $this->checkIn();
        }

        if (isset($_POST['get_tool_by_barcode_ajax'])) {
            $this->getToolByBarcodeAjax();
        }

        if (isset($_POST['new_subscription'])) {
            $this->newSubscription();
        }
    }

    private function newSubscription(): void {
        $session = $this->RCMS->StripeWrapper->getStripeClient()->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'T-shirt',
                    ],
                    'unit_amount' => 2000,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => 'https://example.com/success',
            'cancel_url' => 'https://example.com/cancel',
        ]);

        $this->RCMS->Functions->outputAJAXResult(200, [ 'id' => $session->id ]);
    }

    /**
     * Returnerer true hvis begge arrays, $a og $b, er ens, ellers false.
     * @param array $a
     * @param array $b
     * @return bool
     */
    public function array_equal(array $a, array $b): bool {
        return (
            is_array($a)
            && is_array($b)
            && count($a) === count($b)
            && array_diff($a, $b) === array_diff($b, $a)
        );
    }

    /**
     * Tilføj et Check-In af et værktøj på en bruger, via POST request
     * @return void
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function checkIn(): void {
        $userID = $this->RCMS->Login->getUserID();

        $response = ['result' => ''];
        $barcode = $_POST['tool_barcode'] ?? null;

        if ($this->RCMS->Login->isLoggedIn() === false) {
            $response['result'] = 'Du er ikke logget ind';
            $this->RCMS->Functions->outputAJAXResult(200, $response);
        }

        if (is_string($barcode) === false || strlen($barcode) !== 13) {
            $response['result'] = 'Stregkode er ikke 13 karakterer';
            $this->RCMS->Functions->outputAJAXResult(200, $response);
        }

        $tool = $this->getToolByBarcode($barcode);
        if (empty($tool)) {
            $response['result'] = 'Intet værktøj fundet med den stregkode';
            $this->RCMS->Functions->outputAJAXResult(200, $response);
        }

        $toolID = $tool['ToolID'];
        if ($this->isToolCheckedIn($toolID) || $this->isToolReserved($toolID, $userID)) {
            $response['result'] = 'Værktøjet er allerede udlånt eller reserveret';
            $this->RCMS->Functions->outputAJAXResult(200, $response);
        }

        $userProduct = $this->getUserProduct($userID);
        if ($userProduct === false) {
            $response['result'] = 'Du har ikke noget abonnement';
            $this->RCMS->Functions->outputAJAXResult(200, $response);
        }

        if ($this->hasUserReachedMaxCheckouts($userID)) {
            $response['result'] = 'Du har allerede udlånt det antal værktøj som dit abonnement tillader';
            $this->RCMS->Functions->outputAJAXResult(200, $response);
        }

        // Alt validering foretaget
        // Tilføj checkin

        $checkInDuration = (int) $userProduct['metadata']['MaxCheckoutDays'];

        $this->RCMS->execute('CALL addCheckIn(?, ?, ?)', array('iii', $userID, $toolID, $checkInDuration));

        $response['result'] = 'success';
        $this->RCMS->Functions->outputAJAXResult(200, $response);
    }

    /**
     * Tjekker om et værktøj er udlånt
     * @param int $toolID
     * @return bool
     */
    public function isToolCheckedIn(int $toolID): bool {
        $res = $this->RCMS->execute('SELECT fn_isToolCheckedIn(?) AS isToolCheckedIn', array('i', $toolID));
        return (bool) $res->fetch_object()->isToolCheckedIn;
    }

    /**
     * Tjekker om et værktøj er reserveret
     *
     * Returnerer false hvis det er brugeren selv, $userID, som har ejer reservationen
     * @param int $toolID
     * @param int $userID
     * @return bool
     */
    public function isToolReserved(int $toolID, int $userID): bool {
        $res = $this->RCMS->execute('SELECT fn_isToolReserved(?, ?) AS isToolReserved', array('ii', $toolID, $userID));
        return (bool) $res->fetch_object()->isToolReserved;
    }

    /**
     * Returnerer hvor mange udlån brugeren har
     * @param int $userID
     * @return bool
     */
    public function getCheckInCountForUser(int $userID): bool {
        $res = $this->RCMS->execute('SELECT fn_getCheckInCountForUser(?) AS TOOL_COUNT', array('i', $userID));
        return (bool) $res->fetch_object()->TOOL_COUNT;
    }

    /**
     * Returnerer brugerens abonnement
     * @param int $userID
     * @return array|bool
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function getUserProduct(int $userID) {
        $userProduct = null;
        $user = $this->getUserByID($userID);

        if ($user['StripeID']) {
            $productID = $this->RCMS->StripeWrapper->getProductIDForCustomer($user['StripeID']);

            if ($productID) {
                $userProduct = $this->RCMS->StripeWrapper->getStripeProduct($productID);

                if (!empty($userProduct)) {
                    return $userProduct;
                }

                return false;
            }
        }

        return false;
    }

    /**
     * Tjekker om en bruger har udlånt det antal værktøj som deres abonnement tillader
     * @param int $userID
     * @return bool
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function hasUserReachedMaxCheckouts(int $userID): bool {
        $userProduct = $this->getUserProduct($userID);

        if ($userProduct) {
            $maxCheckOuts = (int) $userProduct['metadata']['MaxCheckouts'];
            $userCurrentCheckOut = $this->getCheckInCountForUser($userID);

            if ($userCurrentCheckOut >= $maxCheckOuts) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Henter et værktøj ud fra databasen hvor værktøjets stregkode er lig $barcode
     * @param string $barcode
     * @return array|null
     */
    public function getToolByBarcode(string $barcode): ?array {
        $res = $this->RCMS->execute('CALL getToolByBarcode(?)', array('s', $barcode));
        return $res->fetch_assoc();
    }

    /**
     * Henter en bruger ud fra databasen
     * @param int $userID ID på den bruger som skal hentes ud
     * @return array|null
     */
    public function getUserByID(int $userID): ?array {
        $res = $this->RCMS->execute('CALL getUserByID(?)', array('i', $userID));
        return $res->fetch_assoc();
    }

    /**
     * Redigerer en bruger via en POST request
     * Bruges både når almindelige brugere ændre deres profil, og når personale ændre på andre brugeres profil
     * @return void
     */
    private function editUser(): void {
        if (!is_numeric($_POST['user_id'])) {
            return;
        }

        $userID = (int) $_POST['user_id'];

        if (!$this->authorizeUser($userID)) {
            return;
        }

        //TODO: Tilføj et ekstra felt, "confirm password" og tjek at de er ens

        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $zipcode = $_POST['zipcode'];
        $city = $_POST['city'];
        $level = $this->RCMS->Login::STANDARD_USER_LEVEL;

        if ($this->RCMS->Login->isAdmin()) {
            $level = $_POST['level'];
        }

        $currentUser = $this->getUserByID($userID);

        // Tjek om brugeren vil ændre sin e-mail, og om e-mailen er optaget
        if ($currentUser['Email'] !== $email) {
            $exists = $this->RCMS->execute('CALL getUserByEmail(?)', array('s', $email));
            if ($exists->num_rows !== 0) {
                // E-mail er allerede taget
                header("Location: ?userid=$userID&emailtaken");
                return;
            }
        }

        // Tjek om brugeren vil ændre sit password
        if (isset($_POST['password']) && $_POST['password'] !== '') {
            $password = $this->RCMS->Login->saltPass($_POST['password']);
        } else {
            $password = $currentUser['Password'];
        }

        $this->RCMS->execute('CALL editUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array('issssssssi', $userID, $firstname, $lastname, $email, $password, $phone, $address, $zipcode, $city, $level));

        if ($userID === $this->RCMS->Login->getUserID()) {
            // Brugerens information har ændret sig, så de skal opdateres i sessionen
            $user = $this->getUserByID($userID);
            unset($user['Password']);
            $_SESSION['user'] = $user;
        }

        $this->editCustomerInStripe($currentUser['StripeID'], $firstname, $lastname, $email, $phone, $address, $zipcode, $city);

        Functions::setNotification('Gemt', 'Dine ændringer blev gemt');

        header('Location: /dashboard');
    }

    /**
     * Wrapper funktion til at redigere en brugers oplysninger i Stripe
     * @param string $stripeCustomerID
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param string $phone
     * @param string $address
     * @param string $zipcode
     * @param string $city
     */
    private function editCustomerInStripe(string $stripeCustomerID, string $firstname, string $lastname, string $email, string $phone, string $address, string $zipcode, string $city): void {
        $params = [
            'name' => $firstname . ' ' . $lastname,
            'email' => $email,
            'phone' => $phone,
            'address' => [
                'line1' => $address,
                'city' => $city,
                'postal_code' => $zipcode,
                'country' => 'DK'
            ],
            'shipping' => [
                'address' => [
                    'line1' => $address,
                    'city' => $city,
                    'postal_code' => $zipcode
                ],
                'name' => $firstname . ' ' . $lastname,
                'phone' => '45' . $phone
            ]
        ];

        $this->RCMS->StripeWrapper->editCustomer($stripeCustomerID, $params);
    }

    /**
     * Fjerner alle kategorier fra et værktøj
     * @param int $toolID
     * @return void
     */
    private function removeAllCategoriesFromTool(int $toolID): void {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $this->RCMS->execute('CALL removeAllCategoriesFromTool(?)', array('i', $toolID));
    }

    /**
     * Returnerer false, hvis $userID ikke er det samme som brugerens ID i databasen og brugeren ikke er personale.
     *
     * Personale kan ændre på alle brugere, så $userID må i de tilfælde godt være et andet ID end det som står i databasen.
     *
     * Funktionen returnerer altid false hvis brugeren ikke er logget ind
     * @param int $userID
     * @return bool
     */
    public function authorizeUser(int $userID): bool {
        if (!$this->RCMS->Login->isLoggedIn()) {
            return false;
        }

        if ($this->RCMS->Login->isAdmin() === false && $userID !== $this->RCMS->Login->getUserID()) {
            return false;
        }

        return true;
    }

    /**
     * Tilføjer en producent via en POST request
     * @return void
     */
    private function addManufacturer(): void {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $manufacturerName = $_POST['manufacturer_name'];

        $this->RCMS->execute('CALL addManufacturer(?)', array('s', $manufacturerName));

        Functions::setNotification('Oprettet', 'Producenten blev oprettet');

        header('Location: /dashboard');
    }

    /**
     * Returnerer en producent
     * @param int $manufacturerID
     * @return array|null
     */
    public function getManufacturer(int $manufacturerID): ?array {
        $res = $this->RCMS->execute('CALL getManufacturer(?)', array('i', $manufacturerID));
        return $res->fetch_assoc();
    }

    /**
     * Redigerer en producent via en POST request
     * @return void
     */
    private function editManufacturer(): void {
        if (!is_numeric($_POST['manufacturer_id']) || !$this->RCMS->Login->isAdmin()) {
            return;
        }

        $manufacturerID = (int) $_POST['manufacturer_id'];
        $manufacturerName = $_POST['manufacturer_name'];

        $this->RCMS->execute('CALL editManufacturer(?, ?)', array('is', $manufacturerID, $manufacturerName));

        Functions::setNotification('Gemt', 'Dine ændringer blev gemt');

        header('Location: /dashboard');
    }

    /**
     * Tilføjer en kategori via en POST request
     * @return void
     */
    private function addCategory(): void {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $categoryName = $_POST['category_name'];

        $this->RCMS->execute('CALL addCategory(?)', array('s', $categoryName));

        Functions::setNotification('Oprettet', 'Kategorien blev oprettet');

        header('Location: /dashboard');
    }

    /**
     * Henter en kategori ud fra databasen
     * @param int $categoryID
     * @return array|null
     */
    public function getCategory(int $categoryID): ?array {
        $res = $this->RCMS->execute('CALL getCategory(?)', array('i', $categoryID));
        return $res->fetch_assoc();
    }

    /**
     * Redigerer en kategori via en POST request
     * @return void
     */
    private function editCategory(): void {
        if (!is_numeric($_POST['category_id']) || !$this->RCMS->Login->isAdmin() ) {
            return;
        }

        $categoryID = (int) $_POST['category_id'];
        $categoryName = $_POST['category_name'];

        $this->RCMS->execute('CALL editCategory(?, ?)', array('is', $categoryID, $categoryName));

        Functions::setNotification('Gemt', 'Dine ændringer blev gemt');

        header('Location: /dashboard');
    }

    /**
     * Redigerer et værktøj via en POST request
     * @return void
     * @throws Exception
     */
    private function editTool(): void {
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

            if ($this->array_equal($categories, $currentToolCategoryIDs) === false) {
                // opdater kategorier
                $this->removeAllCategoriesFromTool($toolID);
                foreach ($categories as $categoryID) {
                    if (!is_numeric($categoryID)) {
                        continue;
                    }
                    $this->addToolToCategory($toolID, (int) $categoryID);
                }
            }
        }

        // Tjek om billedet skal opdateres
        $imageName = $_FILES['image']['name'] ?? false;
        if ($imageName) {
            // opdater billede
            $newImageName = $this->uploadImage($imageName, $_FILES['image']['tmp_name']);
            if (!$newImageName) {
                return;
            }
        } else {
            // brug gamle billede
            $newImageName = $currentTool['Image'];
        }

        $this->RCMS->execute('CALL editTool(?, ?, ?, ?, ?, ?)', array('issisi', $manufacturerID, $toolName, $description, $status, $newImageName, $toolID));

        Functions::setNotification('Gemt', 'Dine ændringer blev gemt');

        header('Location: /dashboard');
    }

    /**
     * Uploader et billede for et værktøj via en POST request, bruger $_FILES array
     * @param string $imageName
     * @param string $tmpName
     * @return bool|string
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
    private function addTool(): void {
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
            $this->addToolToCategory($toolID, (int) $categoryID);
        }

        Functions::setNotification('Oprettet', 'Værktøjet blev oprettet');

        header('Location: /dashboard');
    }

    /**
     * Tilføjer et værktøj til en kategori
     * @param int $toolID
     * @param int $categoryID
     * @return void
     */
    private function addToolToCategory(int $toolID, int $categoryID): void {
        $this->RCMS->execute('CALL addToolToCategory(?, ?)', array('ii', $toolID, $categoryID));
    }

    /**
     * Henter et værktøj ud fra databasen
     * @param int $toolID
     * @return array|false
     */
    public function getToolByID(int $toolID) {
        $res = $this->RCMS->execute('CALL getToolByID(?)', array('i', $toolID));
        $tool = $res->fetch_assoc();

        $tool['Categories'] = $this->getCategoriesForTool($tool['ToolID']);

        if ($tool === null) {
            return false;
        }

        return $tool;
    }

    /**
     * Henter et værktøj ud af databasen via stregkoden, og udskriver resultatet i JSON.
     * Til brug ved AJAX requests.
     */
    private function getToolByBarcodeAjax(): void {
        if (!isset($_POST['tool_barcode']) || strlen($_POST['tool_barcode']) !== 13) {
            $this->RCMS->Functions->outputAJAXResult(400, ['result' => 'Stregkode er forkert']);
        }

        // Indsæt hash i session og databasen med bruger ID, tjek efterfølgende på det i checkIn() metoden

        $tool = $this->getToolByBarcode($_POST['tool_barcode']);

        $tool['Image'] = $this->RELATIVE_TOOL_IMAGE_FOLDER . '/' . $tool['Image'];

        $result = [
            'result' => 'success',
            'tool' => $tool
        ];

        $this->RCMS->Functions->outputAJAXResult(200, $result);
    }

    /**
     * Henter alle værktøj ud fra databasen
     * @return array
     */
    public function getAllTools(): array {
        $res = $this->RCMS->execute('CALL getAllTools();');

        $tools = $res->fetch_all(MYSQLI_ASSOC);

        foreach ($tools as $key => $tool) {
            $tools[$key]['Categories'] = $this->getCategoriesForTool($tool['ToolID']);
        }

        return $tools ?? [];
    }

    /**
     * Returnerer mængden af værktøj der er i databasen (med filtre), til brug ved pagination
     * @return int
     */
    public function getToolCountWithFilters(): int {
        $filters = $this->getPaginationFilters();

        return (int) $this->RCMS->execute('SELECT fn_GetToolCountBySearch(?, ?, ?) as toolCount', array('ssi', $filters['search-text'], $filters['categories'], $filters['only_in_stock']))->fetch_object()->toolCount;
    }

    /**
     * Udskriver links til forsiden så man kan skifte side og se flere værktøj
     */
    public function displayFrontPagePagination(): void {
        $rowCount = $this->getToolCountWithFilters();
        $pages = ceil($rowCount / self::TOOLS_PER_PAGE);

        $query = $this->getFilterQueryString();

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
     * Udskriver beskeden på forsiden hvor der står "Viser 1 - x af x på side x"
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
        $vars = explode('&', $_SERVER['QUERY_STRING']);

        $final = array();

        if (!empty($vars)) {
            foreach($vars as $var) {
                if (empty($var)) {
                    continue;
                }

                $parts = explode('=', $var);

                $key = $parts[0];
                $val = $parts[1];

                if (!array_key_exists($key, $final) && $key !== 'pagenum') {
                    $final[$key] = $val;
                }

            }
        }

        return http_build_query($final);
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
     * Henter værktøj ud af databasen, med mulighed for at filtrere på kategorier og søgetekst
     * @return array
     */
    public function getAllToolsWithFilters(): array {
        $filters = $this->getPaginationFilters();

        $res = $this->RCMS->execute('CALL getToolsBySearch(?, ?, ?, ?)', array('ssii', $filters['search-text'], $filters['categories'], $filters['only_in_stock'], $filters['pagenum']));
        $tools = $res->fetch_all(MYSQLI_ASSOC);

        foreach ($tools as $key => $tool) {
            $tools[$key]['Categories'] = $this->getCategoriesForTool($tool['ToolID']);
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
     * Henter alle kategorier ud fra databasen
     * @return array
     */
    public function getAllCategories(): array {
        $res = $this->RCMS->execute('CALL getAllCategories()');

        return $res->fetch_all(MYSQLI_ASSOC) ?? [];
    }

    /**
     * Henter alle kategorier ud for et værktøj fra databasen
     * @param int $toolID
     * @return array
     */
    public function getCategoriesForTool(int $toolID): array {
        $res = $this->RCMS->execute('CALL getCategoriesForTool(?)', array('i', $toolID));

        return $res->fetch_all(MYSQLI_ASSOC) ?? [];
    }

    /**
     * Henter alle producenter ud fra databasen
     * @return array
     */
    public function getAllManufacturers(): array {
        $res = $this->RCMS->execute('CALL getAllManufacturers()');
        return $res->fetch_all(MYSQLI_ASSOC) ?? [];
    }

    /**
     * Henter en bruger ud af databasen via deres e-mail
     * @param string $email
     * @return bool|array
     */
    public function getUserByEmail(string $email) {
        $res = $this->RCMS->execute('CALL getUserByEmail(?)', array('s', $email));

        return $res->fetch_array(MYSQLI_ASSOC) ?? false;
    }
}