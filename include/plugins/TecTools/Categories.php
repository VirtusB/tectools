<?php

declare(strict_types=1);

/**
 * Class Categories
 * Denne klasse indeholder metoder som vedrører kategorier på TecTools siden
 * Den indeholder metoder til bl.a. oprette, redigere og hente kategorier
 */
class Categories {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    /**
     * @var bool $disableAutoLoading
     * Forhindrer RCMS at loade denne klasse automatisk
     */
    public static bool $disableAutoLoading;

    /**
     * Liste over POST endpoints (metoder), som kan eksekveres automatisk
     * Vi er nød til at have en liste over tilladte endpoints, så brugere ikke kan eksekvere alle metoder i denne klasse
     * @var array|string[]
     */
    public static array $allowedEndpoints = [
        'addCategory', 'editCategory', 'deleteCategory'
    ];

    /**
     * @var TecTools $TecTools
     */
    public TecTools $TecTools;

    public function __construct(TecTools $TecTools) {
        $this->TecTools = $TecTools;
        $this->RCMS = $TecTools->RCMS;

        $TecTools->POSTClasses[] = $this;
    }

    /**
     * Tilføjer en kategori via en POST request
     * @return void
     */
    public function addCategory(): void {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $categoryName = $_POST['category_name'];

        if ($this->categoryExists($categoryName)) {
            Helpers::setNotification('Fejl', 'Kategorien eksisterer allerede', 'error');
            return;
        }

        $this->RCMS->execute('CALL addCategory(?)', array('s', $categoryName));

        $this->RCMS->Logs->addLog(Logs::CREATE_CATEGORY_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Oprettet', 'Kategorien blev oprettet');

        Helpers::redirect('/dashboard');
    }

    /**
     * Sletter en kategori via POST request
     */
    public function deleteCategory(): void {
        $categoryID = (int) $_POST['category_id'];

        if ($this->isCategoryInUse($categoryID)) {
            Helpers::setNotification('Fejl', 'Kategorien er i brug', 'error');
            return;
        }

        $this->RCMS->execute('CALL removeCategory(?)', array('i', $categoryID));
        Helpers::setNotification('Succes', 'Kategorien blev slettet');
    }

    /**
     * Tjekker om en kategori bliver benyttet på nuværende tidspunkt
     * @param int $categoryID
     * @return bool
     */
    private function isCategoryInUse(int $categoryID): bool {
        $inUse = $this->RCMS->execute('SELECT fn_IsCategoryInUse(?) AS inUse', array('i', $categoryID))->fetch_object()->inUse;
        return (bool) $inUse;
    }

    /**
     * Tjekker om en kategori med samme navn allerede eksisterer i databasen
     * @param $name
     * @return bool
     */
    private function categoryExists(string $name): bool {
        $exists = $this->RCMS->execute('SELECT fn_CategoryExists(?) AS count', array('s', $name))->fetch_object()->count;
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
    public function editCategory(): void {
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

        $this->RCMS->Logs->addLog(Logs::EDIT_CATEGORY_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

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