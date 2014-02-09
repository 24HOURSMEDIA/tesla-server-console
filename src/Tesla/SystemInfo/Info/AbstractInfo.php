<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 09:10
 */

namespace Tesla\SystemInfo\Info;


use Tesla\SystemInfo\Exception\SystemInfoException;

abstract class AbstractInfo
{


    const SYSTEMINFO_TYPE_VALUE = 1;
    const SYSTEMINFO_TYPE_ARRAY = 2;

    /**
     * @var string
     */
    private $type;

    /**
     * Singular value (if applicable)
     * @var
     */
    private $value;

    /**
     * Items (of applicable)
     * @var array
     */
    private $items = array();

    /**
     * Get the Type
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * Set the items
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Get the Items
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    public function addItem($item)
    {
        $this->items[] = $item;

        return $this;
    }


}