<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 08/02/14
 * Time: 13:00
 */

namespace Tesla\WebserverConsole\Panel;


class Panel
{

    private $uid;
    private $title;
    private $items;

    /**
     * Set the title
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the Title
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the items
     * @param mixed $items
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Get the Items
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    public function addItem($item)
    {
        $this->items[] = $item;
    }

    /**
     * Set the uid
     * @param mixed $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get the Uid
     * @return mixed
     */
    public function getUid()
    {
        return $this->uid;
    }


}