<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 06/02/14
 * Time: 23:14
 */

namespace Tesla\SystemInfo\Monitor;


class CpuUsageMonitor implements MonitorInterface
{

    private function getStats()
    {
        $stat1 = file('/proc/stat');
        sleep(1);
        $stat2 = file('/proc/stat');
        $info1 = explode(" ", preg_replace("!cpu +!", "", $stat1[0]));
        $info2 = explode(" ", preg_replace("!cpu +!", "", $stat2[0]));
        $dif = array();
        $dif['user'] = $info2[0] - $info1[0];
        $dif['nice'] = $info2[1] - $info1[1];
        $dif['sys'] = $info2[2] - $info1[2];
        $dif['idle'] = $info2[3] - $info1[3];
        $total = array_sum($dif);
        $cpu = array();
        foreach ($dif as $x => $y) {
            $cpu[$x] = round($y / $total * 100, 1);
        }

        return $cpu;
    }

    /**
     * Gets the value of the monitor
     * @return mixed
     */
    function getValue($type = 'user')
    {
        $stats = $this->getStats();
        switch ($type) {
            case 'user':
                return $stats['user'];
            case 'nice':
                return $stats['nice'];
            case 'sys':
                return $stats['sys'];
            case 'idle':
                return $stats['idle'];
            default:
                throw new MonitorException('CpuUsageMonitor: invalid type ' . $type . ' - must be user,nice,sys or idle');
        }
    }

    /**
     * Get a more comprehensive monitor result
     * @return MonitorResult
     */
    function getResult($type = "user")
    {
        $value = $this->getValue($type);
        $title = 'cpu ' . $type . ' (%)';
        $inverse = $type == 'idle';

        return MonitorResult::create($title, $value)->setMin(0)->setMax(100)->setInverse($inverse);
    }


} 