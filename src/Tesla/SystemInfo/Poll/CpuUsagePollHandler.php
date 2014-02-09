<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 06/02/14
 * Time: 23:14
 */

namespace Tesla\SystemInfo\Poll;

use Tesla\SystemInfo\Exception\PollException;

class CpuUsagePollHandler implements PollHandlerInterface
{

    private $stats = null;

    private function getStats()
    {
        if ($this->stats) {
            return $this->stats;
        }
        $output = array();
        exec('top -b -n 2 |grep ^Cpu', $output);
        $lines = explode(' ', $output[1]);
        $stats = array('user' => -1, 'system' => -1, 'nice' => -1, 'idle' => -1, 'wait' => -1, 'steal' => -1);
        foreach ($lines as $line) {
            if (strpos($line, 'us')) {
                $stats['user'] = (float)$line;
            }
            if (strpos($line, 'system')) {
                $stats['system'] = (float)$line;
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

        return $this->stats = $stats;


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
            case 'system':
                return $stats['system'];
            case 'idle':
                return $stats['idle'];
            case 'wait':
                return $stats['wait'];
            case 'steal':
                return $stats['steal'];
            case 'used':
                return 100 - $stats['idle'];
            default:
                throw new PollException('CpuUsagePollHandler: invalid type ' . $type . ' - must be user,nice,sys or idle');
        }
    }

    /**
     * Get a more comprehensive monitor result
     * @return PollResult
     */
    function getResult($type = "user")
    {
        $value = $this->getValue($type);
        $title = 'cpu ' . $type . ' (%)';
        $inverse = $type == 'idle';

        return PollResult::create($title, (float)sprintf('%0.2f', $value))->setMin(0)->setMax(100)->setUnit(
            '%'
        )->setInverse($inverse);
    }


} 