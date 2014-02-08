<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 07/02/14
 * Time: 09:36
 */

namespace Tesla\SystemInfo\Poll;


use Tesla\SystemInfo\Exception\PollException;

/**
 * Class NetPortConnectionsPollHandler
 * Monitor listening, established, all connections on a port or all ports
 *
 * @package Tesla\SystemInfo\Monitor
 */
class NetPortConnectionsPollHandler implements PollHandlerInterface
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
        if (!in_array($state, array('ALL', 'LISTEN', 'ESTABLISHED', 'WAIT'))) {
            throw new PollException('state must be one of all, listen, established,wait');
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
     * @return PollResult
     */
    function getResult($port = 'all', $state = 'all')
    {
        $max = 400; // reasonable amount of ports listening
        if ($state == 'all') {
            $max = $max * 4;
        }
        if ($port == 'all') {
            $max = $max * 4; // 6 is num running services
        }


        $stateTitles = array('ALL' => 'ALL', 'ESTABLISHED' => 'EST', 'WAIT' => 'WAIT', 'LISTEN' => 'LISTEN');
        $title = 'port :' . $port . ' ' . $stateTitles[strtoupper($state)];

        return PollResult::create(
            $title,
            $this->getValue($port, $state)
        )->setMin(0)->setMax($max);
    }


} 