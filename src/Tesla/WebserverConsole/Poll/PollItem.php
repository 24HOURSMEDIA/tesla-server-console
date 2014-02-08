<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 08/02/14
 * Time: 13:17
 */

namespace Tesla\WebserverConsole\Poll;


class PollItem
{

    private $uid;
    private $title;
    private $pollUrl;
    private $display;
    private $refreshInterval;

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
     * Set the pollUrl
     * @param mixed $pollUrl
     */
    public function setPollUrl($pollUrl)
    {
        $this->pollUrl = $pollUrl;

        return $this;
    }

    /**
     * Get the PollUrl
     * @return mixed
     */
    public function getPollUrl()
    {
        return $this->pollUrl;
    }

    /**
     * Set the display
     * @param mixed $display
     */
    public function setDisplay($display)
    {
        $this->display = $display;

        return $this;
    }

    /**
     * Get the Display
     * @return mixed
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * Set the refreshInterval
     * @param mixed $refreshInterval
     */
    public function setRefreshInterval($refreshInterval)
    {
        $this->refreshInterval = $refreshInterval;

        return $this;
    }

    /**
     * Get the RefreshInterval
     * @return mixed
     */
    public function getRefreshInterval()
    {
        return $this->refreshInterval;
    }


}