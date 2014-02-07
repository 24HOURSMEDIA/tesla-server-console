<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 06/02/14
 * Time: 21:35
 */

namespace Tesla\SystemInfo\Monitor;


use Tesla\SystemInfo\Exception\MonitorException;

class LoadAvgMonitor implements MonitorInterface
{

    private $numCores = 1;
    private $maxLevel = 1;

    function __construct(CpuCoresMonitor $cpuCores)
    {
        $this->numCores = $cpuCores->getValue();
        $this->maxLevel = $this->numCores;
    }

    function getValue($interval = 1)
    {
        $load = sys_getloadavg();
        switch ($interval) {
            case 1:
                return $load[0];
            case 5:
                return $load[1];
            case 15:
                return $load[2];
            default:
                throw new MonitorException('LoadAvgMonitor: invalid interval ' . $interval . ' - must be 1, 5 or 15');
        }

    }

    function getResult($interval = 1)
    {
        $value = $this->getValue($interval);
        $title = 'load avg. ' . $interval . ' min';

        return MonitorResult::create($title, (float)sprintf('%0.2f', $value))->setMin(0)->setMax($this->maxLevel);
    }

} 