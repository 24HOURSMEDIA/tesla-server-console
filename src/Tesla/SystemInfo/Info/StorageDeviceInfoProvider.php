<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 09:19
 */

namespace Tesla\SystemInfo\Info;


class StorageDeviceInfoProvider extends AbstractInfoProvider
{


    /**
     * @return AbstractInfo
     */
    function getInfo($type = null)
    {
        $info = new StorageDeviceInfo();
        $result = $this->exec('df -h');
        for ($i = 1; $i < count($result); $i++) {
            // clean phrase
            $parts = explode(' ', $result[$i]);
            foreach ($parts as $k => $v) {
                if (in_array($v, array('', ' ', chr(8)))) {
                    unset($parts[$k]);
                }
            }
            $parts = array_values($parts);

            $item = array(
                'filesystem' => $parts[0],
                'mountedOn' => $parts[5],
                'usePct' => (int)$parts[4],
                'size' => $this->convertToMegabytes($parts[1]),
                'use' => $this->convertToMegabytes($parts[2]),
                'available' => $this->convertToMegabytes($parts[3])
            );
            $info->addItem($item);

        }

        return $info;
    }

    /**
     * @return array
     */
    function getSupportedTypes()
    {
        return array(null);
    }


} 