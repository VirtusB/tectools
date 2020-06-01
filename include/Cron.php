<?PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/../index.php');

class Cron {
	var $RCMS;
	private $cronjobs;
	
	function __construct($RCMS) {
		$this->RCMS = $RCMS;
		$this->cronjobs = array();
	}
	
	function runCronJobs(){
		foreach ($this->cronjobs as $job){
			$time = $job[0];
			//Se om den skal køres med if
			$job[1]();
		}
		die();
	}
	
	function addCronJob($cronjob){
		$this->cronjobs[] = $cronjob;
	}
}
?>