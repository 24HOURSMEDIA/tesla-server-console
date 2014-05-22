<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 22/05/14
 * Time: 12:03
 */

namespace Tesla\WebserverConsole\Controller;
use Silex\Application;
use Tesla\AwsConsole\Extensions\AbstractExtension;

abstract class AbstractController {

    /**
     * @var array
     */
    private $configs;

    /**
     * @var Application
     */
    private $container;

    function setContainer(Application $container) {
        $this->container = $container;
        return $this;
    }

    function loadConfiguration($configs) {
        $configs = (array)$configs;
        $this->configs = $configs;
        return $this;
    }

    /**
     * @param $id
     * @return AbstractExtension
     */
    function getExtension($id) {

        if ($this->container->offsetExists('tesla_server_console.extension.' . $id)) {

            $extension = $this->container['tesla_server_console.extension.' . $id];
            /* @var $extension AbstractExtension */
            if ($extension->isEnabled()) {

                return $extension;
            }
        }
    }

    /**
     * Extends the view parameters with specific extensions..
     *
     * @param $vars
     * @return mixed
     */
    function extendViewParameters($vars) {


        if ($extension = $this->getExtension('tesla_awscloud_integration')) {
            $vars = $extension->extendViewParameters($vars);
        }

        return $vars;
    }


} 