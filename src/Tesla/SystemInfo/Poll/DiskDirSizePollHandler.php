<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 15:28
 */

namespace Tesla\SystemInfo\Poll;


class DiskDirSizePollHandler implements PollHandlerInterface
{
    /**
     * Gets the value of the monitor
     * @return mixed
     */
    function getValue($dir = null)
    {
        exec('du -ks ' . escapeshellarg($dir), $l);

        return (int)$l[0];

    }

    /**
     * Get a more comprehensive monitor result
     * @return PollResult
     */
    function getResult($dir = null)
    {
        return PollResult::create('Dir ' . $dir, (int)($this->getValue($dir) / 1024))->setUnit('Mb');

    }
} 