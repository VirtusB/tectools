<?php

require_once(__DIR__ . '/../index.php');

/**
 * Class Cron
 * Denne klasse står for at håndtere og køre cronjobs.
 *
 * For at klassen fungere, skal der manuelt tilføjes et job til crontab'en på Linux serveren, som skal køre denne fil.
 * Denne fil skal køres ligeså ofte, som det job man tilføjer med denne klasse der køres oftest.
 *
 * Forklaring: Hvis man gerne vil tilføje et job med denne klasse, som skal køres hvert 5. minut,
 * så skal jobbet man manuelt har tilføjet til crontab'en køre denne fil minimum ligeså ofte.
 * Man må gerne køre denne fil endnu oftere, som f.eks. hvert minut, det må bare ikke være mindre hyppigt.
 *
 * Til TecTools projektet køre vi denne fil hvert minut,
 * hvilket betyder at denne klasse tjekker 5 gange i minuttet om vores job til at slette reservationer skal køres.
 * Vores job til at slette reservationer bliver stadig kun kørt 1 gang hvert 5. minut.
 */
class Cron {
    /**
     * @var RCMS $RCMS
     */
    public RCMS $RCMS;

    private array $cronjobs;

    public function __construct($RCMS) {
        $this->RCMS = $RCMS;
        $this->cronjobs = array();
    }

    public function runCronJobs(): void {
        foreach ($this->cronjobs as $job) {
            $time = $job[0];
            if ($this->is_time_cron(time(), $time)) {
                $job[1]();
            }
        }
        die();
    }

    public function addCronJob($cronjob): void {
        $this->cronjobs[] = $cronjob;
    }

    /**
     * Tjekker om et tidsstempel matcher et cron format
     * F.eks. $cron = '5 0 * * *';
     * Denne funktion er taget fra: https://www.binarytides.com/php-check-if-a-timestamp-matches-a-given-cron-schedule/
     * @param $time
     * @param $cron
     * @return bool
     */
    private function is_time_cron($time, $cron): bool {
        $cron_parts = explode(' ', $cron);
        if (count($cron_parts) != 5) {
            return false;
        }

        list($min, $hour, $day, $mon, $week) = explode(' ', $cron);

        $to_check = array('min' => 'i', 'hour' => 'G', 'day' => 'j', 'mon' => 'n', 'week' => 'w');

        $ranges = array('min' => '0-59', 'hour' => '0-23', 'day' => '1-31', 'mon' => '1-12', 'week' => '0-6',);

        foreach ($to_check as $part => $c) {
            $val = $$part;
            $values = array();

            /*
                For mønstre som 0-23/2
            */
            if (strpos($val, '/') !== false) {
                list($range, $steps) = explode('/', $val);

                if ($range == '*') {
                    $range = $ranges[$part];
                }
                list($start, $stop) = explode('-', $range);

                for ($i = $start; $i <= $stop; $i += $steps) {
                    $values[] = $i;
                }
            } /*
            For mønsre som:
            2
            2,5,8
            2-23
        */
            else {
                $k = explode(',', $val);

                foreach ($k as $v) {
                    if (strpos($v, '-') !== false) {
                        list($start, $stop) = explode('-', $v);

                        for ($i = $start; $i <= $stop; $i++) {
                            $values[] = $i;
                        }
                    } else {
                        $values[] = $v;
                    }
                }
            }

            if (!in_array(date($c, $time), $values) and (strval($val) != '*')) {
                return false;
            }
        }

        return true;
    }
}
