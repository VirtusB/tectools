<?php

declare(strict_types=1);

class GlobalHandlers {
	public RCMS $RCMS;
	
	public function __construct(RCMS $RCMS) {
		$this->RCMS = $RCMS;
	}

    /**
     * Tjekker om $function eksisterer i denne klasse, kalder funktionen hvis den gør og returnerer værdien fra det kald
     * Hvis funktionen ikke eksisterer bliver false returneret
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
     * @param array $level
     * @return string
     */
    private function formatUserLevel(array $level): string {
        $level = $level[0];

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
     * Returnerer HTML <img> element af værktøjets billede
     * @param array $image
     * @return string
     */
    private function showToolImage(array $image): string {
        $image = $image[0];

        /**
         * @var TecTools $TecTools
         */
        $TecTools = $GLOBALS['TecTools'];
        $imgSrc = $TecTools->RELATIVE_TOOL_IMAGE_FOLDER . '/' . $image;

        return <<<HTML
            <img style="max-height: 53.75px;" src="$imgSrc" alt="">
HTML;
    }

    /**
     * Returnerer HTML <svg> element af stregkode
     * @param array $barcode
     * @return string
     */
    private function showToolBarcode(array $barcode): string {
        $barcode = $barcode[0];


        return <<<HTML
            <svg class="barcode" style=" width: 48mm; height: 10mm; background-color: white;margin: 0; padding: 0; " 
            jsbarcode-format="ean13"
            jsbarcode-value="$barcode" jsbarcode-textmargin="0"
            jsbarcode-marginleft="0" jsbarcode-marginright="0" jsbarcode-margintop="0"
            jsbarcode-marginbottom="0" jsbarcode-height="20"></svg>
HTML;
    }
}
