<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 21:05
 */

namespace Tesla\SystemInfo\Poll;

use Tesla\SystemInfo\Poll\PollResult;

class PhpApcStatPollHandler implements PollHandlerInterface
{

    private $fileCacheStats;
    private $userCacheStats;

    /**
     * Gets the value of the monitor
     * @return mixed
     */
    function getValue($type = null, $key = null)
    {
        if (!$this->fileCacheStats) {
            $this->fileCacheStats = apc_cache_info('file_hits', true);
        }
        if (!$this->userCacheStats) {
            $this->userCacheStats = apc_cache_info('user', true);
        }
        $source = $type == 'user' ? $this->userCacheStats : $this->fileCacheStats;;

        switch ($key) {
            case 'miss_ratio':
                return 100 * $source['num_misses'] / $source['num_hits'];
            case "mem_size":
                return sprintf('%0.2f', $source['mem_size'] / 1024 / 1024);
        }

        return $source[$key];
    }

    /**
     * Get a more comprehensive monitor result
     * @return PollResult
     */
    function getResult($type = null, $key = null)
    {
        $result = PollResult::create($key, sprintf('%0.2f', $this->getValue($type, $key)));
        if ($key == 'miss_ratio') {
            $result->setMax(100)->setUnit('%');
        }
        if ($key == 'mem_size') {
            $result->setMax(256)->setUnit('Mb');
        }

        return $result;
    }

} 