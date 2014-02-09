<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 06/02/14
 * Time: 21:33
 */

namespace Tesla\SystemInfo\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Tesla\SystemInfo\Info\StorageDeviceInfoProvider;
use Tesla\SystemInfo\Poll\CpuCoresPollHandler;
use Tesla\SystemInfo\Poll\CpuUsagePollHandler;
use Tesla\SystemInfo\Poll\DiskDirSizePollHandler;
use Tesla\SystemInfo\Poll\DiskHighestUsagePollHandler;
use Tesla\SystemInfo\Poll\DiskUsagePollHandler;
use Tesla\SystemInfo\Poll\LoadAvgPollHandler;
use Tesla\SystemInfo\Poll\NetPortConnectionsPollHandler;
use Tesla\SystemInfo\Poll\PhpApcStatPollHandler;
use Tesla\SystemInfo\Poll\PollResult;

class SilexSystemInfoServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['tesla_systeminfo_cpucores.poll_handler'] = $app->share(
            function () {
                return new CpuCoresPollHandler();
            }
        );
        $app['tesla_systeminfo_loadavg.poll_handler'] = $app->share(
            function () use ($app) {
                return new LoadAvgPollHandler($app['tesla_systeminfo_cpucores.poll_handler']);
            }
        );
        $app['tesla_systeminfo_cpuusage.poll_handler'] = $app->share(
            function () use ($app) {
                return new CpuUsagePollHandler();
            }
        );
        $app['tesla_systeminfo_netportconnections.poll_handler'] = $app->share(
            function () use ($app) {
                return new NetPortConnectionsPollHandler();
            }
        );
        $app['tesla_systeminfo_diskusage.poll_handler'] = $app->share(
            function () use ($app) {
                return new DiskUsagePollHandler($app['tesla_systeminfo_info.storagedeviceinfo_provider']);
            }
        );
        $app['tesla_systeminfo_diskhighestusage.poll_handler'] = $app->share(
            function () use ($app) {
                return new DiskHighestUsagePollHandler($app['tesla_systeminfo_info.storagedeviceinfo_provider']);
            }
        );


        $app['tesla_systeminfo_diskdirsize.poll_handler'] = $app->share(
            function () use ($app) {
                return new DiskDirSizePollHandler();
            }
        );

        $app['tesla_systeminfo_php_apc_stat.poll_handler'] = $app->share(
            function () use ($app) {
                return new PhpApcStatPollHandler();
            }
        );

        // informational
        $app['tesla_systeminfo_info.storagedeviceinfo_provider'] = $app->share(
            function () use ($app) {
                return new StorageDeviceInfoProvider();
            }
        );


    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        $routePrefix = '/tesla/system-info/poll';
        $serializer = $app['serializer'];

        $slowCachetime = 15;
        $defaultCachetime = 10;
        $fastCachetime = 1;
        $extraLongCachetime = 3600;

        $app->get(
            $routePrefix . '/loadavg/{interval}',
            function ($interval) use ($app, $serializer, $fastCachetime, $slowCachetime, $defaultCachetime) {
                $result = $app['tesla_systeminfo_loadavg' . '.poll_handler']->getResult($interval);
                $json = $serializer->serialize($result, 'json');
                $response = Response::create($json)->setPrivate()->setMaxAge($fastCachetime);
                $response->headers->set('content-type', 'application/json');

                return $response;
            }
        )->bind('tesla_systeminfo_poll_loadavg');
        $app->get(
            $routePrefix . '/cpuusage/{type}',
            function ($type) use ($app, $serializer, $fastCachetime, $slowCachetime, $defaultCachetime) {
                $result = $app['tesla_systeminfo_cpuusage' . '.poll_handler']->getResult($type);
                $json = $serializer->serialize($result, 'json');
                $response = Response::create($json)->setPrivate()->setMaxAge($slowCachetime);
                $response->headers->set('content-type', 'application/json');

                return $response;
            }
        )->bind('tesla_systeminfo_poll_cpuusage');
        $app->get(
            $routePrefix . '/netportconnections/{port}/{state}',
            function ($port, $state) use ($app, $serializer, $fastCachetime, $slowCachetime, $defaultCachetime) {
                $result = $app['tesla_systeminfo_netportconnections.poll_handler']->getResult($port, $state);
                $json = $serializer->serialize($result, 'json');
                $response = Response::create($json)->setPrivate()->setMaxAge($fastCachetime);
                $response->headers->set('content-type', 'application/json');

                return $response;
            }
        )->bind('tesla_systeminfo_poll_netportconnections');
        $app->get(
            $routePrefix . '/disks/max',
            function () use ($app, $serializer, $fastCachetime, $slowCachetime, $defaultCachetime) {
                $result = $app['tesla_systeminfo_diskhighestusage.poll_handler']->getResult();
                $json = $serializer->serialize($result, 'json');
                $response = Response::create($json)->setPrivate()->setMaxAge($fastCachetime);
                $response->headers->set('content-type', 'application/json');

                return $response;

            }
        )->bind('tesla_systeminfo_poll_disks_maxusage');

        $app->get(
            $routePrefix . '/disks/usage/{device}',
            function ($device) use ($app, $serializer, $fastCachetime, $slowCachetime, $defaultCachetime) {
                $result = $app['tesla_systeminfo_diskusage.poll_handler']->getResult($device);
                $json = $serializer->serialize($result, 'json');
                $response = Response::create($json)->setPrivate()->setMaxAge($slowCachetime);
                $response->headers->set('content-type', 'application/json');

                return $response;

            }
        )->bind('tesla_systeminfo_diskusage')->assert('device', '[\w\-\._/]+');

        $app->get(
            $routePrefix . '/disks/dirsize/{dir}',
            function ($dir) use ($app, $serializer, $fastCachetime, $slowCachetime, $defaultCachetime) {
                $result = $app['tesla_systeminfo_diskdirsize.poll_handler']->getResult($dir);
                $json = $serializer->serialize($result, 'json');
                $response = Response::create($json)->setPrivate()->setMaxAge($slowCachetime);
                $response->headers->set('content-type', 'application/json');

                return $response;

            }
        )->bind('tesla_systeminfo_diskdirsize')->assert('dir', '[\w\-\._/]+');

        $app->get(
            $routePrefix . '/php/apc-stat/cache/{type}/stat/{key}',
            function ($key, $type) use ($app, $serializer, $fastCachetime, $slowCachetime, $defaultCachetime) {
                $result = $app['tesla_systeminfo_php_apc_stat.poll_handler']->getResult($type, $key);
                $json = $serializer->serialize($result, 'json');
                $response = Response::create($json)->setPrivate()->setMaxAge($fastCachetime);
                $response->headers->set('content-type', 'application/json');

                return $response;

            }
        )->bind('tesla_systeminfo_php_apc_stat');


        $infoRoutePrefix = '/tesla/system-info/info';
        $app->get(
            $infoRoutePrefix . '/disks/info/{infoType}',
            function ($infoType) use ($app, $serializer, $fastCachetime, $slowCachetime, $defaultCachetime) {
                $result = $app['tesla_systeminfo_info.storagedeviceinfo_provider']->getInfo($infoType);
                $json = $serializer->serialize($result, 'json');
                $response = Response::create($json)->setPrivate()->setMaxAge(0);
                $response->headers->set('content-type', 'application/json');

                return $response;
            }
        )->value('infoType', false)->bind('tesla_systeminfo_info_disks');


    }


} 