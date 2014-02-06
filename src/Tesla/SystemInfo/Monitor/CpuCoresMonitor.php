<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 06/02/14
 * Time: 22:02
 */

namespace Tesla\SystemInfo\Monitor;


class CpuCoresMonitor implements MonitorInterface
{

    /**
     * Gets the value of the monitor
     * @return mixed
     */
    function getValue()
    {
        $numCpus = 1;

        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);

            $numCpus = count($matches[0]);
        } else {
            if ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
                $process = @popen('wmic cpu get NumberOfCores', 'rb');

                if (false !== $process) {
                    fgets($process);
                    $numCpus = intval(fgets($process));

                    pclose($process);
                }
            } else {
                $process = @popen('sysctl -a', 'rb');

                if (false !== $process) {
                    $output = stream_get_contents($process);

                    preg_match('/hw.ncpu: (\d+)/', $output, $matches);
                    if ($matches) {
                        $numCpus = intval($matches[1][0]);
                    }

                    pclose($process);
                }
            }
        }

        return $numCpus;
    }

    /**
     * Get a more comprehensive monitor result
     * @return MonitorResult
     */
    function getResult()
    {
        $cores = $this->getValue();

        return MonitorResult::create('cpu cores', $cores)->setMin(0)->setMax($cores);
    }


}