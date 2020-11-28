<?php

declare(strict_types=1);

/**
 * Class Template
 * Denne klasse står primært for at vise indhold til brugeren
 * Den tjekker også på om brugeren må se det indhold de anmoder om
 * Andre metoder der omhandler indhold kan tilføjes i denne klasse
 */
class Template {
    /**
     * @var RCMS $RCMS
     */
	public RCMS $RCMS;
	
	public function __construct(RCMS $RCMS) {
		$this->RCMS = $RCMS;
	}

    /**
     * Loader den side som brugeren gerne vil se, fra databasen, hvis den eksisterer og brugeren har adgang
     *
     * Adgang har to niveauer
     *
     * Hvis require_login er sat til true, kan man kun se siden hvis man er logget ind
     *
     * Hvis is_admin_page er sat til true, kan man kun se siden hvis man er admin
     * @return void
     */
	public function display_content(): void {
		$request = $this->RCMS->getRequestedPage();

		if ($request === null) {
			$this->display_404();
			return;
		}

		if (isset($request['is_admin_page']) && $request['is_admin_page'] && !$this->RCMS->Login->isAdmin()) {
			$request = __DIR__ . '/..' . $this->RCMS->getTemplateFolder() . "/layout/login.php";
			if (!file_exists($request)) {
				echo "Fejl ved loading af side! Kontakt en administrator hvis problemet genopstår. (siden eksisterer ikke)";
				return;
			}
			require_once($request);
			return;
		} else if (isset($request['require_login']) && $request['require_login'] && !$this->RCMS->Login->isLoggedIn()) {
			$request = __DIR__ . '/..' . $this->RCMS->getTemplateFolder() . "/layout/login.php";
			if (!file_exists($request)) {
				echo "Fejl ved loading af side! Kontakt en administrator hvis problemet genopstår. (Du er ikke logget ind)";
				return;
			}
			require_once($request);
			return;
		}

		if ($request['include']) {
			$request = __DIR__ . '/..' . $this->RCMS->getTemplateFolder() . "/layout" . $request['content'];
			if (!file_exists($request)) {
				echo "Fejl ved loading af side! Kontakt en administrator hvis problemet genopstår. (Indhold mangler)";
				return;
			}
			require_once($request);
		} else {
			$request['content'];
		}
	}

    /**
     * Hvis en side ikke eksisterer, benyttes denne metode for at give besked til brugeren
     *
     * Sætter den HTTP status som browseren modtager til 404 Page Not Found
     * @return void
     */
	public function display_404(): void {
	    http_response_code(404);
		echo "404 - Siden blev ikke fundet!";
	}
}
