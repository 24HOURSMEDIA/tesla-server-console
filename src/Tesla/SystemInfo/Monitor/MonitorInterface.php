<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 06/02/14
 * Time: 21:41
 */

namespace Tesla\SystemInfo\Monitor;


interface MonitorInterface
{

    /**
     * Gets the value of the monitor
     * @return mixed
     */
    function getValue();

    /**
     * Get a more comprehensive monitor result
     * @return MonitorResult
     */
    function getResult();
} 