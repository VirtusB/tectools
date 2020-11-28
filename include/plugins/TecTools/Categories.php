<?php

class Categories extends Base {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    public static bool $disableAutoLoading;

    public static array $allowedEndpoints = [
        'addCategory', 'editCategory'
    ];

    public TecTools $TecTools;

    public function __construct(TecTools $TecTools) {
        $this->TecTools = $TecTools;
        $this->RCMS = $TecTools->RCMS;

        parent::__construct();
    }

    /**
     * Tilføjer en kategori via en POST request
     * @return void
     */
    protected function addCategory(): void {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $categoryName = $_POST['category_name'];

        if ($this->categoryExists($categoryName)) {
            Helpers::setNotification('Fejl', 'Kategorien eksisterer allerede', 'error');
            return;
        }

        $this->RCMS->execute('CALL addCategory(?)', array('s', $categoryName));

        $this->RCMS->addLog(LogTypes::CREATE_CATEGORY_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Oprettet', 'Kategorien blev oprettet');

        Helpers::redirect('/dashboard');
    }

    /**
     * Tjekker om en kategori med samme navn allerede eksisterer i databasen
     * @param $name
     * @return bool
     */
    private function categoryExists($name): bool {
        $exists = $this->RCMS->execute('SELECT COUNT(*) AS count FROM Categories WHERE CategoryName = ?', array('s', $name))->fetch_object()->count;
        return $exists !== 0;
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
    protected function editCategory(): void {
        if (!is_numeric($_POST['category_id']) || !$this->RCMS->Login->isAdmin() ) {
            return;
        }

        $categoryID = (int) $_POST['category_id'];
        $categoryName = $_POST['category_name'];

        if ($this->categoryExists($categoryName)) {
            Helpers::setNotification('Fejl', 'Kategorien eksisterer allerede', 'error');
            return;
        }

        $this->RCMS->execute('CALL editCategory(?, ?)', array('is', $categoryID, $categoryName));

        $this->RCMS->addLog(LogTypes::EDIT_CATEGORY_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Gemt', 'Dine ændringer blev gemt');

        Helpers::redirect('/dashboard');
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
     * Tilføjer et værktøj til en kategori
     * @param int $toolID
     * @param int $categoryID
     * @return void
     */
    public function addToolToCategory(int $toolID, int $categoryID): void {
        $this->RCMS->execute('CALL addToolToCategory(?, ?)', array('ii', $toolID, $categoryID));
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
     * Henter alle kategorier ud fra databasen
     * @return array
     */
    public function getAllCategories(): array {
        $res = $this->RCMS->execute('CALL getAllCategories()');

        return $res->fetch_all(MYSQLI_ASSOC) ?? [];
    }
}