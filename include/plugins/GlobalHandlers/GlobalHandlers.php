<?php

declare(strict_types=1);

/**
 * Class GlobalHandlers
 * Denne klasse indeholder funktioner som bruges i pluginet RCMSTables.
 * Tabeller oprettet med RCMSTables kan konfigureres således, at der køres en funktion på hver kolonne inden tabellen bliver udskrevet på siden.
 * Disse funktioner defineres i denne klasse.
 *
 * Vi har f.eks. en funktion der hedder "formatUserLevel", som kigger på brugerens niveau fra databasen
 * Det giver ikke mening for brugere på siden at se et 1-tal eller et 9-tal, for de ved ikke hvad forskellen er
 * Med formatUserLevel formaterer vi 1-tallet til "Standard" og 9-tallet til "Personale"
 */
class GlobalHandlers {
	public RCMS $RCMS;
	
	public function __construct(RCMS $RCMS) {
		$this->RCMS = $RCMS;
	}

    /**
     * Tjekker om $function eksisterer i denne klasse, kalder metoden hvis den gør og returnerer værdien fra det kald
     * Hvis metoden ikke eksisterer bliver false returneret
     * @param string $function
     * @param array $args
     * @return bool|mixed
     */
	public function callFunction(string $function, array $args = array()) {
		if (method_exists($this, $function)) {
		    return $this->$function($args);
		}

		return false;
	}

    /**
     * Returnerer "Standard" eller "Personale", alt efter om hvad niveau brugeren er på
     * @param array $data
     * @return string
     */
    private function formatUserLevel(array $data): string {
        $level = $data[0];

        switch ($level) {
            case 1:
                return 'Standard';
                break;
            case 9:
                return 'Personale';
                break;
        }

        return 'N/A';
    }

    /**
     * Returnerer "Betalt" eller "Ikke betalt", alt efter om bøden er betalt
     * @param array $data
     * @return string
     */
    private function formatFineStatus(array $data): string {
        $status = $data[0];

        switch ($status) {
            case 1:
                return 'Betalt';
                break;
            case 0:
                return 'Ikke betalt';
                break;
        }

        return 'N/A';
    }

    /**
     * Formaterer en pris så det inkluderer "DKK" og ",-"
     * @param array $data
     * @return string
     */
    private function formatPaymentAmount(array $data): string {
        $amount = (float) $data[0];

        return 'DKK ' . $amount . ',-';
    }

    /**
     * Tilføjet et link til værktøjsnavnet
     * @param array $data
     * @return string
     */
    private function addLinkToToolName(array $data): string {
        $row = $data[1];
        $toolName = $row['ToolName'];
        $toolID = $row['ToolID'] ?? $row['FK_ToolID'];

        return <<<HTML
        <a target="_blank" href="/tools/view?toolid=$toolID">$toolName</a>
HTML;
    }

    /**
     * Returnerer HTML <img> element af værktøjets billede
     * @param array $image
     * @return string
     */
    private function showToolImage(array $data): string {
        $image = $data[0];

        /**
         * @var TecTools $TecTools
         */
        $TecTools = $GLOBALS['TecTools'];
        $imgSrc = $TecTools->RELATIVE_TOOL_IMAGE_FOLDER . '/' . $image;

        return <<<HTML
            <img class="materialboxed" style="max-height: 53.75px;" src="$imgSrc" alt="">
HTML;
    }

    /**
     * Returnerer HTML <svg> element af stregkode
     * @param array $barcode
     * @return string
     */
    private function showToolBarcode(array $data): string {
        $barcode = $data[0];

        return <<<HTML
            <svg class="barcode" style=" width: 48mm; height: 10mm; background-color: white;margin: 0; padding: 0; " 
            jsbarcode-format="ean13"
            jsbarcode-value="$barcode" jsbarcode-textmargin="0"
            jsbarcode-marginleft="0" jsbarcode-marginright="0" jsbarcode-margintop="0"
            jsbarcode-marginbottom="0" jsbarcode-height="20"></svg>
HTML;
    }
}
