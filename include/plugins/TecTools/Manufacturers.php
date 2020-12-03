<?php

declare(strict_types=1);

/**
 * Class Manufacturers
 * Denne klasse indeholder metoder som vedrører producenter på TecTools siden
 * Den indeholder metoder til bl.a. oprette, redigere og hente producenter
 */
class Manufacturers {
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
        'addManufacturer', 'editManufacturer', 'deleteManufacturer'
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
     * Tilføjer en producent via en POST request
     * @return void
     */
    public function addManufacturer(): void {
        if (!$this->RCMS->Login->isAdmin()) {
            return;
        }

        $manufacturerName = $_POST['manufacturer_name'];

        if ($this->manufacturerExists($manufacturerName)) {
            Helpers::setNotification('Fejl', 'Producenten eksisterer allerede', 'error');
            return;
        }

        $this->RCMS->execute('CALL addManufacturer(?)', array('s', $manufacturerName));

        $this->RCMS->Logs->addLog(Logs::CREATE_MANUFACTURER_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

        Helpers::setNotification('Oprettet', 'Producenten blev oprettet');

        Helpers::redirect('/dashboard');
    }

    /**
     * Sletter en producent via POST request
     */
    public function deleteManufacturer(): void {
        $manufacturerID = (int) $_POST['manufacturer_id'];

        if ($this->isManufacturerInUse($manufacturerID)) {
            Helpers::setNotification('Fejl', 'Producenten er i brug', 'error');
            return;
        }

        $this->RCMS->execute('CALL removeManufacturer(?)', array('i', $manufacturerID));
        Helpers::setNotification('Succes', 'Producenten blev slettet');
    }

    /**
     * Tjekker om en producent bliver benyttet på nuværende tidspunkt
     * @param int $manufacturerID
     * @return bool
     */
    private function isManufacturerInUse(int $manufacturerID): bool {
        $inUse = $this->RCMS->execute('SELECT fn_IsManufacturerInUse(?) AS inUse', array('i', $manufacturerID))->fetch_object()->inUse;
        return (bool) $inUse;
    }

    /**
     * Tjekker om en producent med samme navn allerede eksisterer i databasen
     * @param $name
     * @return bool
     */
    private function manufacturerExists(string $name): bool {
        $exists = $this->RCMS->execute('SELECT fn_ManufacturerExists(?) AS count', array('s', $name))->fetch_object()->count;
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
    public function editManufacturer(): void {
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

        $this->RCMS->Logs->addLog(Logs::EDIT_MANUFACTURER_TYPE_ID, ['UserID' => $this->RCMS->Login->getUserID()]);

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