<?PHP
class GlobalHandlers {
	var $RCMS;
	
	function __construct($RCMS) {
		$this->RCMS = $RCMS;
	}
	
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
                return 'Administrator';
                break;
        }

        return 'N/A';
    }

    function formatStatus($status){
        $status = $status[0];

        /**
         * @var $TecTools TecTools
         */
        $TecTools = $GLOBALS['TecTools'];

        $statusList = $TecTools->getStatusList();
        $status = array_filter($statusList, function ($s) use($status) {
            if (is_numeric($status)) {
                return $s['id'] === intval($status);
            }
            return false;
        });

        $status = reset($status);

        return $status['name'];
    }

    function showToolImage($image){
        $image = $image[0];

        /**
         * @var $TecTools TecTools
         */
        $TecTools = $GLOBALS['TecTools'];
        $imgSrc = $TecTools->RELATIVE_TOOL_IMAGE_FOLDER . '/' . $image;

        return <<<HTML
            <img style="max-height: 53.75px;" src="$imgSrc" alt="">
HTML;
    }
}
