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

        //TODO: tilføj et ekstra felt, "confirm password" og tjek at de er ens

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

        header('Location: /dashboard');
    }

    public function editCustomerInStripe(string $stripeCustomerID, string $firstname, string $lastname, string $email, string $phone, string $address, string $zipcode, string $city) {
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
    public function removeAllCategoriesFromTool(int $toolID): void {
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

        header('Location: /dashboard');
    }

    /**
     * Tilføjer et værktøj til en kategori
     * @param int $toolID
     * @param int $categoryID
     * @return void
     */
    public function addToolToCategory(int $toolID, int $categoryID): void {
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

    private function getToolByBarcodeAjax() {
        if (!isset($_POST['tool_barcode']) || strlen($_POST['tool_barcode']) !== 13) {
            $this->RCMS->Functions->outputAJAXResult(400, ['result' => 'Stregkode er forkert']);
        }

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
     * Henter værktøj ud af databasen, med mulighed for at filtrere på kategorier og søgetekst
     * @param array $filters
     * @return array
     */
    public function getAllToolsWithFilters(array $filters): array {
        $query = <<<SQL
        SELECT * FROM Tools p1
        LEFT JOIN Manufacturers p2 ON p1.FK_ManufacturerID = p2.ManufacturerID
        LEFT JOIN Statuses p3 ON p3.StatusID = p1.FK_StatusID
        WHERE
SQL;


        $searchText = '';
        $categories = [];
        $parameters = [];
        $types = '';

        $hasSearchText = false;
        $hasCategories = false;

        if (isset($filters['search-text']) && !empty($filters['search-text'])) {
            $searchText = $filters['search-text'];
            $hasSearchText = true;
        }

        if (isset($filters['categories']) && !empty($filters['categories'])) {
            $categories = array_map(static fn($category) => (int) $category, $filters['categories']);
            $hasCategories = true;
        }

        if ($hasSearchText && !$hasCategories) {
            $types = 'ssss';
            $parameters = array_fill(0, 4, "%$searchText%");

            $query .= <<<SQL
            p1.ToolName LIKE ?
            OR p1.Description LIKE ?
            OR p2.ManufacturerName LIKE ?
            OR p3.StatusName LIKE ?
SQL;
        } else if ($hasCategories && !$hasSearchText) {
            $IN = str_repeat('?,', count($categories) - 1) . '?';
            $types = str_repeat('i', count($categories));
            $parameters = $categories;

            $query .= <<<SQL
            p1.ToolID IN (SELECT FK_ToolID FROM CategoryTools WHERE FK_ToolID = p1.ToolID AND FK_CategoryID IN($IN))
SQL;
        } else if ($hasCategories && $hasSearchText) {
            $IN = str_repeat('?,', count($categories) - 1) . '?';
            $types = str_repeat('i', count($categories)) . 'ssss';
            $parameters = array_merge($categories, array_fill(0, 4, "%$searchText%"));

            $query .= <<<SQL
            p1.ToolID IN (SELECT FK_ToolID FROM CategoryTools WHERE FK_ToolID = p1.ToolID AND FK_CategoryID IN($IN))
            AND (p1.ToolName LIKE ?
            OR p1.Description LIKE ?
            OR p2.ManufacturerName LIKE ?
            OR p3.StatusName LIKE ?)
SQL;
        }

        $res = $this->RCMS->execute($query, [$types, ...$parameters]);
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

    public function getUserByEmail(string $email) {
        $res = $this->RCMS->execute('CALL getUserByEmail(?)', array('s', $email));

        return $res->fetch_array(MYSQLI_ASSOC) ?? false;
    }
}