<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 09:09
 */

namespace Tesla\SystemInfo\Info;

use Tesla\SystemInfo\Exception\SystemInfoException;

abstract class AbstractInfoProvider
{

    /**
     * @return AbstractInfo
     */
    abstract function getInfo($type = null);

    /**
     * @return array
     */
    abstract function getSupportedTypes();


    /**
     * Check if type is in the array of supported types, or throw runtime exception
     * @param $type
     * @throws \Tesla\SystemInfo\Exception\SystemInfoException
     */
    protected function checkIfTypeIsSupported($type)
    {
        if (!in_array($typ, $this->getSupportedTypes())) {
            throw new SystemInfoException('Type ' . $type . ' is not provided .');
        }
    }

    final protected function exec($cmd)
    {
        exec($cmd, $result);
        if (!is_array($result)) {
            throw new SystemInfoException('Could not exec ' . $cmd);
        }

        return $result;
    }

    function convertToMegabytes($expr)
    {
        if (!$expr) {
            return 0;
        }
        $val = (int)$expr;
        $multiplier = substr($expr, -1, 1);

        switch (strtoupper($multiplier)) {
            case 'K':
                $val = $val * 1024;
                break;
            case 'M':
                $val = $val * 1024 * 1024;
                break;
            case 'G':
                $val = $val * 1024 * 1024 * 1024;
                break;
            case 'T':
                $val = $val * 1024 * 1024 * 1024 * 1024;
                break;
            default:

        }

        return $val / 1024 / 1024;
    }

} 