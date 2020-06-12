<?PHP
class Template {
    /**
     * @var RCMS $RCMS
     */
	var $RCMS;
	
	function __construct(RCMS $RCMS) {
		$this->RCMS = $RCMS;
	}

    /**
     * Loader den side som brugeren gerne vil se, fra databasen, hvis den eksisterer og brugeren har adgang
     * Adgang har to niveauer
     * Hvis require_login er sat til true, kan man kun se siden hvis man er logget ind
     * Hvis is_admin_page er sat til true, kan man kun se siden hvis man er admin
     */
	function display_content() {
		$request = $this->RCMS->getRequestedPage();

		if ($request == null) {
			$this->display_404();
			return;
		}

		if (isset($request['is_admin_page']) && $request['is_admin_page'] && !$this->RCMS->Login->isAdmin()) {
			$request = __DIR__ . '/..' . $this->RCMS->getTemplateFolder() . "/layout/login.php";
			if (!file_exists($request)) {
				echo "Fejl ved loading af side! Kontakt en administrator hvis problemet genopstår.";
				return;
			}
			require_once($request);
			return;
		}else if (isset($request['require_login']) && $request['require_login'] && !$this->RCMS->Login->isLoggedIn()) {
			$request = __DIR__ . '/..' . $this->RCMS->getTemplateFolder() . "/layout/login.php";
			if (!file_exists($request)) {
				echo "Fejl ved loading af side! Kontakt en administrator hvis problemet genopstår.";
				return;
			}
			require_once($request);
			return;
		}

		if ($request['include']) {
			$request = __DIR__ . '/..' . $this->RCMS->getTemplateFolder() . "/layout" . $request['content'];
			if (!file_exists($request)) {
				echo "Fejl ved loading af side! Kontakt en administrator hvis problemet genopstår.";
				return;
			}
			require_once($request);
		}else{
			$request['content'];
		}
	}

    /**
     * Hvis en side ikke eksisterer, bruges den funktion efterfølgende
     * Sætter den HTTP status som browseren modtager til 404 Page Not Found
     */
	function display_404() {
	    http_response_code(404);
		echo "404 - Siden blev ikke fundet!";
	}

    /**
     * Denne funktion køres kun 1 gang inden indhold i template bliver inkluderet, og sørger for at alt på siden, bortset fra MySQL data, er i UTF-8 format
     */
	function initEncoding() {
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');
        header('Content-Type: text/html; charset=UTF-8');
    }
}
