<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 06/02/14
 * Time: 16:30
 */

namespace Tesla\Silex\ConfigurationManager;

use Symfony\Component\Filesystem\Filesystem;
use Tesla\Silex\ConfigurationManager\Exception\ConfigurationException;


class ConfigurationManager
{

    private $parameters = array();
    private $config = null;
    private $parameterFile;
    private $isLoaded = false;
    private $confFiles = array();

    public function __construct($parameterFile)
    {
        $fs = new Filesystem();
        $this->parameterFile = $parameterFile;
        if (!$fs->exists($parameterFile)) {
            throw new ConfigurationException('parameter file or configuration directory not found');
        }

    }


    public function registerConfigFiles(array $files)
    {
        foreach ($files as $file) {
            $f = realpath($file);
            if (!$f) {
                throw new ConfigurationException("config file " . $file . " not found");
            }
            $this->confFiles[] = $file;
        }
    }

    protected function load()
    {
        if ($this->isLoaded) {
            return;
        }

        $t = microtime(true);
        $parameters = json_decode(file_get_contents($this->parameterFile));
        if (!$parameters) {
            throw new \Exception('Could not load parameters file ' . $parameterFile);
        }

        foreach (get_object_vars($parameters) as $property => $value) {
            $this->parameters[$property] = $value;
        }
        // load all config files
        $config = array();
        foreach ($this->confFiles as $file) {
            $f = json_decode(file_get_contents($file), true);
            if (!$f) {
                throw new ConfigurationException('Could not parse config file ' . $file);
            }
            $config = array_replace_recursive($config, $f);

        }
        $encoded = json_encode($config);
        foreach ($this->parameters as $k => $v) {
            if (!is_array($v) && !is_object($v)) {
                $encoded = str_replace('%' . $k . '%', $v, $encoded);
            } else {
                if ((false !== strpos($encoded, '%' . $k . '%'))) {
                    throw new ConfigurationException('ConfigurationManager does not support object or arrays for substitution - key ' . $k);
                }
            }
        }
        //if (false !== strpos($encoded, '%')) {
        //    throw new ConfigurationException('Unresolved parameters found in configuration.');
        //}
        $this->config = json_decode($encoded, true);
        $t = microtime(true) - $t;
        // echo($t);
        $this->isLoaded = true;
    }


    function getParameter($key)
    {
        $this->isLoaded or $this->load();
        if (!isset($this->parameters[$key])) {
            throw new ConfigurationException('Parameter ' . $key . ' not found');
        }

        return $this->parameters[$key];
    }

    function setParameter($key, $value)
    {
        $this->isLoaded or $this->load();
        $this->parameters[$key] = $value;
        return $this;
    }


    function getSection($section)
    {
        $this->isLoaded or $this->load();
        if (!isset($this->config[$section])) {
            throw new ConfigurationException('Section ' . $section . ' not defined in configuration');
        }

        return $this->config[$section];
    }

    function setSection($section, $cfg)
    {
        $this->config[$section] = $cfg;
    }

    function getSetting($section, $key)
    {
        $this->isLoaded or $this->load();
        $section = $this->getSection($section);
        if (!isset($section[$key])) {
            throw new ConfigurationException('Key ' . $key . ' not found in section ' . $section);
        }

        return $section[$key];
    }

    function merge($parms) {
        $this->isLoaded or $this->load();
        $this->config = array_merge_recursive($this->config, $parms);
        return $this;
    }


} 