<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 12:54
 */

namespace Tesla\WebserverConsole\Panel;


class PanelsetFactory
{

    /**
     * @var array
     */
    private $panelSetsConfig = array();
    private $panelFactory;

    function __construct($panelsConfig, PanelFactory $panelFactory)
    {
        $this->panelSetsConfig = $panelsConfig;
        $this->panelFactory = $panelFactory;
        // validate?
    }

    function get($panelSetId)
    {
        $panels = array();
        $set = $this->panelSetsConfig[$panelSetId];

        foreach ($set['panels'] as $panelId) {
            $panels[] = $this->panelFactory->get($panelId);
        }

        return $panels;
    }
} 