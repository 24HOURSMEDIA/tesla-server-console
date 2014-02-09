<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 22:11
 */

namespace Tesla\SystemInfo\Poll;

use Tesla\SystemInfo\Exception\PollException;

class MemInfoPollHandler implements PollHandlerInterface
{

    private $info;

    protected function getInfo()
    {
        if ($this->info) {
            return $this->info;
        }
        exec('cat /proc/meminfo', $raw);
        $this->info = array();
        foreach ($raw as $line) {
            $line = strtolower($line);
            $parts = explode(':', $line);
            $val = (int)trim($parts[1]);
            if (strpos($parts[1], 'kb')) {
                $val = sprintf('%0.3f', $val / 1024);
            }
            $this->info[trim($parts[0])] = $val;
        }

        return $this->info;
    }

    /**
     * Gets the value of the monitor
     * @return mixed
     */
    function getValue($key = null)
    {
        $info = $this->getInfo();

        return $info[$key];
    }

    /**
     * Get a more comprehensive monitor result
     * @return PollResult
     */
    function getResult($key = null)
    {
        $val = $this->getValue($key);

        $result = PollResult::create($key, $val)->setMax($this->getValue('memtotal'));
        if (!strpos($val, 'pages')) {
            $result->setUnit('Mb');
        }
        if ($key == 'memfree') {
            $result->setInverse(true);
        }

        return $result;
    }


} 