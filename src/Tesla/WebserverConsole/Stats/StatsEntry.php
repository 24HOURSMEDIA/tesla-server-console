<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 19:45
 */

namespace Tesla\WebserverConsole\Stats;


class StatsEntry
{

    private $serviceId;
    private $time;
    private $value;
    private $calcTime;

    static function create()
    {
        $entry = new self();
        $entry->time = time();
        $entry->calcTime = microtime(true);

        return $entry;
    }

    /**
     * Set the calcTime
     * @param mixed $calcTime
     */
    public function setCalcTime($calcTime)
    {
        $this->calcTime = $calcTime;

        return $this;
    }

    /**
     * Get the CalcTime
     * @return mixed
     */
    public function getCalcTime()
    {
        return $this->calcTime;
    }

    /**
     * Set the serviceId
     * @param mixed $serviceId
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;

        return $this;
    }

    /**
     * Get the ServiceId
     * @return mixed
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * Set the time
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get the Time
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set the value
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the Value
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }


    public function finish()
    {
        $this->calcTime = sprintf('%0.4f', microtime(true) - $this->calcTime);
    }


}