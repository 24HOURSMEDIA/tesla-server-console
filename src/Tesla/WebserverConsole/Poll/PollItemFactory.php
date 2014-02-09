<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 08/02/14
 * Time: 12:49
 */

namespace Tesla\WebserverConsole\Poll;

use Symfony\Component\Routing\Generator\UrlGenerator;
use Tesla\WebserverConsole\Exception\WebserverConsoleException;

class PollItemFactory
{

    private $pollConfig = array();
    /**
     * @var \Symfony\Component\Routing\Generator\UrlGenerator
     */
    private $urlGenerator;

    function __construct($pollConfig, UrlGenerator $urlGenerator)
    {
        $this->pollConfig = $pollConfig;
        $this->urlGenerator = $urlGenerator;
        // validate configs?
    }

    /**
     * @param $pollServiceId
     * @return PollItem
     * @throws \Tesla\WebserverConsole\Exception\WebserverConsoleException
     */
    function get($pollServiceId)
    {

        $pollDef = $this->pollConfig[$pollServiceId];
        if (!$pollDef) {
            throw new WebserverConsoleException('Poll definition ' . $pollServiceId . ' not found');
        }

        $pollItem = new PollItem();

        $pollUrl = $this->urlGenerator->generate($pollDef['route']['name'], $pollDef['route']['parameters']);


        $pollItem
            ->setUid(uniqid('poll_'))
            ->setTitle($pollDef['title'])
            ->setPollUrl($pollUrl)
            ->setDisplay('progress')
            ->setRefreshInterval(2000);

        return $pollItem;
    }

} 