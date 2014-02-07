<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 06/02/14
 * Time: 23:14
 */

namespace Tesla\SystemInfo\Monitor;

use Tesla\SystemInfo\Exception\MonitorException;

class CpuUsageMonitor implements MonitorInterface
{

    private function getStats()
    {

        $output = array();
        exec('top -b -n 2 |grep ^Cpu', $output);
        $lines = explode(' ', $output[1]);
        $stats = array('user' => -1, 'sys' => -1, 'nice' => -1, 'idle' => -1, 'wait' => -1);
        foreach ($lines as $line) {
            if (strpos($line, 'us')) {
                $stats['user'] = (float)$line;
            }
            if (strpos($line, 'sy')) {
                $stats['sys'] = (float)$line;
            }
            if (strpos($line, 'ni')) {
                $stats['nice'] = (float)$line;
            }
            if (strpos($line, 'id')) {
                $stats['idle'] = (float)$line;
            }
            if (strpos($line, 'wa')) {
                $stats['wait'] = (float)$line;
            }
            if (strpos($line, 'st')) {
                $stats['steal'] = (float)$line;
            }
        }

        return $stats;


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
            case 'wait':
                return $stats['wait'];
            case 'steal':
                return $stats['steal'];
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