<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 22/05/14
 * Time: 11:53
 */

namespace Tesla\AwsConsole\Extensions;


abstract class AbstractExtension {

    protected $config = array();

    protected $container;

    function loadConfiguration(array $config) {

        $default = $this->getDefaultConfiguration();
        $this->config = new \ArrayObject(array_merge(array('enabled' => false), $default, $config));
        return $config;
    }

    function setContainer($container) {
        $this->container = $container;
        return $this;
    }

    /**
     * @return array
     */
    abstract function getDefaultConfiguration();

    function isEnabled() {

        return isset($this->config['enabled']) && $this->config['enabled'];
    }

    function extendViewParameters(array $parameters) {
        if (!isset($parameters['extensions'])) {
            $parameters['extensions'] = array(
                'submenus' => array()
            );
        }
        return $parameters;
    }



} 