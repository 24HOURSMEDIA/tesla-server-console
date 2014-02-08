<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 08/02/14
 * Time: 12:54
 */

namespace Tesla\WebserverConsole\Panel;


use Tesla\WebserverConsole\Exception\WebserverConsoleException;
use Tesla\WebserverConsole\Poll\PollItemFactory;

class PanelFactory
{

    /**
     * @var array
     */
    private $panelsConfig = array();
    /**
     * @var \Tesla\WebserverConsole\Poll\PollItemFactory
     */
    private $pollItemFactory;

    function __construct($panelsConfig, PollItemFactory $pollItemFactory)
    {
        $this->panelsConfig = $panelsConfig;
        $this->pollItemFactory = $pollItemFactory;
        // validate?
    }

    /**
     * @param $panelId
     * @return Panel
     * @throws \Tesla\WebserverConsole\Exception\WebserverConsoleException
     */
    function get($panelId)
    {
        $panelDefinition = $this->panelsConfig[$panelId];
        if (!$panelDefinition) {
            throw new WebserverConsoleException('Panel definition ' . $panelId . ' not found');
        }
        $panel = new Panel();
        $panel
            ->setUid(uniqid('panel_'))
            ->setTitle($panelDefinition['title']);

        foreach ($panelDefinition['items'] as $itemDef) {
            switch ($itemDef['type']) {
                case 'poll':
                    $item = $this->pollItemFactory->get($itemDef['service']);
                    $panel->addItem($item);
                    break;
                default:
                    throw new WebserverConsoleException('Panel item definition ' . $itemDef['type'] . ' not supported');
            }
        }

        return $panel;

    }
} 