<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 06/02/14
 * Time: 21:42
 */

namespace Tesla\SystemInfo\Poll;


class PollResult
{

    private $value;
    private $min = 0;
    private $max;
    private $title = 'result';
    private $isInverse = false;
    private $unit = '';

    function getId()
    {
        return 'm' . crc32($this->getTitle());
    }

    /**
     * @param $title
     * @param $value
     * @return mixed
     */
    static function create($title, $value)
    {
        $monitor = new self();

        return $monitor->setTitle($title)->setValue($value);
    }

    /**
     * @param bool $inverse
     * @return $this
     */
    function setInverse($inverse = true)
    {
        $this->isInverse = $inverse;

        return $this;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the Title
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $value
     * @return $this
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

    /**
     * @param $max
     * @return $this
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * Get the Max
     * @return mixed
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param $min
     * @return $this
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * Get the Min
     * @return mixed
     */
    public function getMin()
    {
        return $this->min;
    }

    function getCriticalLevel()
    {
        return $this->getMax() * 0.5;
    }

    function getWarningLevel()
    {
        return $this->getCriticalLevel() * 0.5;
    }

    function getWarning()
    {
        return !$this->isInverse ? $this->getValue() > $this->getWarningLevel() : $this->getValue(
            ) < $this->getWarningLevel();
    }

    function getCritical()
    {
        return !$this->isInverse ? $this->getValue() > $this->getCriticalLevel() : $this->getValue(
            ) < $this->getCriticalLevel();
    }

    /**
     * @param $unit
     * @return $this
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get the Unit
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }


} 