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
}
