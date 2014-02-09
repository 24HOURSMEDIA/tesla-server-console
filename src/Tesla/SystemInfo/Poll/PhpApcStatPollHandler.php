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

    /**
     * Gets the value of the monitor
     * @return mixed
     */
    function getValue($key = null)
    {
        if (!$this->fileCacheStats) {
            $this->fileCacheStats = apc_cache_info('file_hits', true);
        }

        switch ($key) {
            case 'miss_ratio':
                return 100 * $this->fileCacheStats['num_misses'] / $this->fileCacheStats['num_hits'];
        }

        return $this->fileCacheStats[$key];
    }

    /**
     * Get a more comprehensive monitor result
     * @return PollResult
     */
    function getResult($key = null)
    {
        $result = PollResult::create($key, sprintf('%0.2f', $this->getValue($key)));
        if ($key = 'miss_ratio') {
            $result->setMax(100)->setUnit('%');
        }

        return $result;
    }

} 