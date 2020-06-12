<?PHP
class GlobalHandlers {
	var $RCMS;
	
	function __construct($RCMS) {
		$this->RCMS = $RCMS;
	}

    /**
     * Tjekker om $function eksisterer i denne klasse, kalder funktionen hvis den gør og returnerer værdien fra det kald
     * Hvis funktionen ikke eksisterer bliver false returneret
     * @param string $function
     * @param array $args
     * @return mixed|bool
     */
	function callFunction($function, $args = array()){
		if (method_exists($this, $function)){
		 return $this->$function($args);
		}else{
			return false;
		}
	}

    function formatUserLevel($level){
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

    function showToolImage($image){
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

    function showToolBarcode($barcode){
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
