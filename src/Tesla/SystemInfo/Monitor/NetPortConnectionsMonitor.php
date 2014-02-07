<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 07/02/14
 * Time: 09:36
 */

namespace Tesla\SystemInfo\Monitor;


use Tesla\SystemInfo\Exception\MonitorException;

/**
 * Class NetPortConnectionsMonitor
 * Monitor listening, established, all connections on a port or all ports
 *
 * @package Tesla\SystemInfo\Monitor
 */
class NetPortConnectionsMonitor implements MonitorInterface
{
    /**
     * Gets the value of the monitor
     * @return mixed
     */
    function getValue($port = 'all', $state = 'all')
    {
        $state = strtoupper($state);
        $port = $port == 'all' ? $port : (int)$port;
        $port = (int)$port;
        if (!in_array($state, array('ALL', 'LISTEN', 'ESTABLISHED'))) {
            throw new MonitorException('state must be one of all, listen, established');
        }
        // make a command
        $cmd = 'netstat -an | grep ' . escapeshellarg($port == 'all' ? ':' : ':' . $port) . ' ';

        if ($state != 'ALL') {
            $cmd .= ' | grep ' . escapeshellarg($state);
        }
        $cmd .= ' | wc -l';
        $output = array();
        exec($cmd, $output);
        return count($output) ? (int)$output[0] : 0;

    }

    /**
     * Get a more comprehensive monitor result
     * @return MonitorResult
     */
    function getResult($port = 'all', $state = 'all')
    {
        $max = 128;
        if ($port == 'all') {
            $max = $max * 8; // 6 is num running services
        }
        if ($state == 'all') {
            $max = $max * 8;
        }

        return MonitorResult::create(
            $state . ' conn. on ' . ($port == 'all' ? 'all ports' : 'port :' . $port),
            $this->getValue($port, $state)
        )->setMin(0)->setMax($max);
    }


} 