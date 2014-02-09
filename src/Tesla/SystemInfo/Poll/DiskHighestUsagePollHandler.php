<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 10:20
 */

namespace Tesla\SystemInfo\Poll;


use Tesla\SystemInfo\Info\StorageDeviceInfoProvider;

class DiskHighestUsagePollHandler implements PollHandlerInterface
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
    function getValue()
    {
        $item = $this->getMax();

        return $item['usePct'];
    }


    function getMax()
    {
        $results = $this->storageDeviceInfoProvider->getInfo()->getItems();

        $max = 0;

        foreach ($results as $key => $a) {
            if ($a['usePct'] >= $max) {
                $selected = $a;
            }
            $max = max($max, $a['usePct']);
        }

        return $selected;
    }

    /**
     * Get a more comprehensive monitor result
     * @return PollResult
     */
    function getResult()
    {
        $max = $this->getMax();

        return PollResult::create('disk with max usage' . $max['filesystem'], $max['usePct'])->setMax(100)->setUnit(
            '%'
        );
    }


} 