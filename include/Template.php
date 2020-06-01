<?PHP
class Template {
	var $RCMS;
	
	function __construct($RCMS) {
		$this->RCMS = $RCMS;
	}

	//Used in template to display the content of the current page, loaded from MySQL
	function display_content() {
		$request = $this->RCMS->getRequestedPage();

		if ($request == null){
			$this->display_404();
			return;
		}

		if (isset($request['is_admin_page']) && $request['is_admin_page'] && !$this->RCMS->Login->isAdmin()){
			$request = __DIR__ . '/..' . $this->RCMS->getTemplateFolder() . "/layout/login.php";
			if (!file_exists($request)){
				echo "Fejl ved loading af side! Kontakt en administrator hvis problemet genopstår.";
				return;
			}
			require_once($request);
			return;
		}else if (isset($request['require_login']) && $request['require_login'] && !$this->RCMS->Login->isLoggedIn()){
			$request = __DIR__ . '/..' . $this->RCMS->getTemplateFolder() . "/layout/login.php";
			if (!file_exists($request)){
				echo "Fejl ved loading af side! Kontakt en administrator hvis problemet genopstår.";
				return;
			}
			require_once($request);
			return;
		}

		if ($request['include']){
			$request = __DIR__ . '/..' . $this->RCMS->getTemplateFolder() . "/layout" . $request['content'];
			if (!file_exists($request)){
				echo "Fejl ved loading af side! Kontakt en administrator hvis problemet genopstår.";
				return;
			}
			require_once($request);
		}else{
			$request['content'];
		}
	}

	function display_404(){
	    http_response_code(404);
		echo "404 - Siden blev ikke fundet!";
	}

	function initEncoding() {
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');
        header('Content-Type: text/html; charset=UTF-8');
    }
}
