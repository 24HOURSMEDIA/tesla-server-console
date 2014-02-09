<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 14:09
 */

namespace Tesla\SystemInfo\Poll;

use Tesla\SystemInfo\Info\StorageDeviceInfoProvider;

class DiskUsagePollHandler implements PollHandlerInterface
{

    /**
     * @var StorageDeviceInfoProvider
     */
    private $storageDeviceInfoProvider;

    function __construct(StorageDeviceInfoProvider $storageDeviceInfoProvider)
    {
        $this->storageDeviceInfoProvider = $storageDeviceInfoProvider;
    }

    /**
     * Gets the value of the monitor
     * @return mixed
     */
    function getValue($device = null)
    {
        $dev = $this->getDevice($device);

        return $dev['usePct'];
    }


    function getDevice($dev)
    {
        $results = $this->storageDeviceInfoProvider->getInfo()->getItems();


        foreach ($results as $key => $inf) {
            if ($inf['filesystem'] == $dev) {
                return $inf;
            }
        }


    }

    /**
     * Get a more comprehensive monitor result
     * @return PollResult
     */
    function getResult($device = null)
    {
        $dev = $this->getDevice($device);

        return PollResult::create('disk usage ' . $device . '(' . $dev['size'] . ' Mb)', $dev['use'])->setMax(
            $dev['size']
        )->setUnit(
            'Mb'
        );
    }


} 