<?php

class Manufacturers extends Base {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    public static bool $disableAutoLoading;

    public static array $allowedEndpoints = [
        'addManufacturer', 'editManufacturer'
    ];

    public TecTools $TecTools;

    public function __construct(TecTools $TecTools) {
        $this->TecTools = $TecTools;
        $this->RCMS = $TecTools->RCMS;

        parent::__construct();
    }

    /**
     * Tilføjer en producent via en POST request
     * @return void
     */
    protected function addManufacturer(): void {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $manufacturerName = $_POST['manufacturer_name'];

        if ($this->manufacturerExists($manufacturerName)) {
            Helpers::setNotification('Fejl', 'Producenten eksisterer allerede', 'error');
            return;
        }

        $this->RCMS->execute('CALL addManufacturer(?)', array('s', $manufacturerName));

        $this->RCMS->addLog(LogTypes::CREATE_MANUFACTURER_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Oprettet', 'Producenten blev oprettet');

        Helpers::redirect('/dashboard');
    }

    /**
     * Tjekker om en producent med samme navn allerede eksisterer i databasen
     * @param $name
     * @return bool
     */
    private function manufacturerExists($name): bool {
        $exists = $this->RCMS->execute('SELECT COUNT(*) AS count FROM Manufacturers WHERE ManufacturerName = ?', array('s', $name))->fetch_object()->count;
        return $exists !== 0;
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
    protected function editManufacturer(): void {
        if (!is_numeric($_POST['manufacturer_id']) || !$this->RCMS->Login->isAdmin()) {
            return;
        }

        $manufacturerID = (int) $_POST['manufacturer_id'];
        $manufacturerName = $_POST['manufacturer_name'];

        if ($this->manufacturerExists($manufacturerName)) {
            Helpers::setNotification('Fejl', 'Producenten eksisterer allerede', 'error');
            return;
        }

        $this->RCMS->execute('CALL editManufacturer(?, ?)', array('is', $manufacturerID, $manufacturerName));

        $this->RCMS->addLog(LogTypes::EDIT_MANUFACTURER_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Gemt', 'Dine ændringer blev gemt');

        Helpers::redirect('/dashboard');
    }

    /**
     * Henter alle producenter ud fra databasen
     * @return array
     */
    public function getAllManufacturers(): array {
        $res = $this->RCMS->execute('CALL getAllManufacturers()');
        return $res->fetch_all(MYSQLI_ASSOC) ?? [];
    }
}