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
use Tesla\SystemInfo\Monitor\CpuCoresMonitor;
use Tesla\SystemInfo\Monitor\CpuUsageMonitor;
use Tesla\SystemInfo\Monitor\LoadAvgMonitor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tesla\SystemInfo\Monitor\NetPortConnectionsMonitor;

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
        $app['tesla_systeminfo_cpucores.monitor'] = $app->share(
            function () {
                return new CpuCoresMonitor();
            }
        );
        $app['tesla_systeminfo_loadavg.monitor'] = $app->share(
            function () use ($app) {
                return new LoadAvgMonitor($app['tesla_systeminfo_cpucores.monitor']);
            }
        );
        $app['tesla_systeminfo_cpuusage.monitor'] = $app->share(
            function () use ($app) {
                return new CpuUsageMonitor();
            }
        );
        $app['tesla_systeminfo_netportconnections.monitor'] = $app->share(
            function () use ($app) {
                return new NetPortConnectionsMonitor();
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
        $routePrefix = '/tesla/system-info';
        $serializer = $app['serializer'];

        $app->get(
            $routePrefix . '/loadavg/{interval}',
            function ($interval) use ($app, $serializer) {
                $result = $app['tesla_systeminfo_loadavg' . '.monitor']->getResult($interval);
                $json = $serializer->serialize($result, 'json');
                $response = Response::create($json);
                $response->headers->set('content-type', 'application/json');

                return $response;
            }
        )->bind('tesla_system_info_loadavg');
        $app->get(
            $routePrefix . '/cpuusage/{type}',
            function ($type) use ($app, $serializer) {
                $result = $app['tesla_systeminfo_cpuusage' . '.monitor']->getResult($type);
                $json = $serializer->serialize($result, 'json');
                $response = Response::create($json);
                $response->headers->set('content-type', 'application/json');

                return $response;
            }
        )->bind('tesla_system_info_cpuusage');
        $app->get(
            $routePrefix . '/netportconnections/{port}/{state}',
            function ($port, $state) use ($app, $serializer) {
                $result = $app['tesla_systeminfo_netportconnections' . '.monitor']->getResult($port, $state);
                $json = $serializer->serialize($result, 'json');
                $response = Response::create($json);
                $response->headers->set('content-type', 'application/json');

                return $response;
            }
        )->bind('tesla_system_info_netportconnections');

        $services = array('cpucores');

        foreach ($services as $serviceId) {
            $app->get(
                $routePrefix . '/' . $serviceId,
                function () use ($app, $serializer, $serviceId) {
                    $result = $app['tesla_systeminfo_' . $serviceId . '.monitor']->getResult();
                    $json = $serializer->serialize($result, 'json');
                    $response = Response::create($json);
                    $response->headers->set('content-type', 'application/json');

                    return $response;
                }
            )->bind('tesla_system_info_' . $serviceId);
        }

    }


} 