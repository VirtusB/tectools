<?php

require_once(__DIR__ . '/../index.php');

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
     * Test if a timestamp matches a cron format or not
     * fx. $cron = '5 0 * * *';
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
                For patters like 0-23/2
            */
            if (strpos($val, '/') !== false) {
                //Get the range and step
                list($range, $steps) = explode('/', $val);

                //Now get the start and stop
                if ($range == '*') {
                    $range = $ranges[$part];
                }
                list($start, $stop) = explode('-', $range);

                for ($i = $start; $i <= $stop; $i += $steps) {
                    $values[] = $i;
                }
            } /*
            For patters like :
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
