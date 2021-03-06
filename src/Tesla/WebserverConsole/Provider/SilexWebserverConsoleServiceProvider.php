<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 07/02/14
 * Time: 11:26
 */

namespace Tesla\WebserverConsole\Provider;


use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tesla\WebserverConsole\Command\CollectStatsCommand;
use Tesla\WebserverConsole\Controller\ConsoleConfigController;
use Tesla\WebserverConsole\Controller\HomeController;
use Tesla\WebserverConsole\Controller\LiveDashboardController;
use Tesla\WebserverConsole\Controller\LogController;
use Symfony\Component\HttpFoundation\Request;
use Tesla\WebserverConsole\Controller\MockController;
use Tesla\WebserverConsole\Exception\WebserverConsoleException;
use Tesla\WebserverConsole\Panel\PanelFactory;
use Tesla\WebserverConsole\Poll\PollItemFactory;
use Tesla\WebserverConsole\Panel\PanelsetFactory;
use Tesla\WebserverConsole\Controller\EtcController;
use Tesla\WebserverConsole\Stats\StatsEntryFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SilexWebserverConsoleServiceProvider implements ServiceProviderInterface
{

    private function processCommands($app, $commands)
    {
        $cfg = $app['config']->getSection('tesla-server-console');
        foreach ($commands as $command => $commandDef) {
            foreach ($commandDef as $params) {
                switch ($command) {

                    case "autoAppendPortPoll":
                        // create poll routes named like tesla_systeminfo_poll_netportconnections_127.0.0.1_80 etc
                        $states = array('all', 'established', 'wait', 'listen');

                        if ($params['createPanel']) {
                            $cfg['panels']['connections_' . $params['ip'] . '_' . $params['port']] = array(
                                'title' => $params['title'],
                                'items' => array()
                            );
                        }
                        foreach ($states as $state) {
                            $serviceId = 'connections_' . $params['ip'] . '_' . $params['port'] . '_' . $state;
                            $cfg['poll'][$serviceId] = array(
                                'title' => $params['title'] . ' ' . $state,
                                'route' => array(
                                    'name' => 'tesla_systeminfo_poll_netportconnections',
                                    'parameters' => array(
                                        'port' => $params['port'],
                                        'ip' => $params['ip'],
                                        'state' => $state
                                    )
                                )
                            );
                            if ($params['createPanel']) {
                                $cfg['panels']['connections_' . $params['ip'] . '_' . $params['port']]['items'][] = array(
                                    'type' => 'poll',
                                    'service' => $serviceId
                                );

                            }
                        }


                        if ($params['createPanel']) {
                            foreach ($params['appendToPanelSets'] as $k => $set) {

                                $cfg["panelsets"][$set]["panels"][] = 'connections_' . $params['ip'] . '_' . $params['port'];
                            }
                        }

                        break;
                    case "appendDiskUsage":
                        $infos = $app['tesla_systeminfo_info.storagedeviceinfo_provider']->getInfo();
                        foreach ($infos->getItems() as $info) {
                            $serviceId = 'disk_' . $info['filesystem'];


                            if ($params['appendToPanels']) {
                                foreach ($params['appendToPanels'] as $panelId) {
                                    $cfg['poll'][$serviceId] = array(

                                        'title' => $info['filesystem'],
                                        'route' => array(
                                            'name' => 'tesla_systeminfo_diskusage',
                                            'parameters' => array(
                                                'device' => $info['filesystem']
                                            )
                                        )
                                    );
                                    $cfg['panels'][$panelId]['items'][] = array(
                                        'type' => 'poll',
                                        'service' => $serviceId
                                    );
                                }
                            }
                        }
                        break;

                    default:
                        throw new WebserverConsoleException('Could not process command ' . $command . ' from config');

                }
            }

        }
        $app['config']->setSection('tesla-server-console', $cfg);
    }

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public
    function register(
        Application $app
    ) {
        $app['tesla_webserverconsole_log.controller'] = $app->share(
            function () use ($app) {
                $controller = new LogController($app['twig']);
                $controller->setContainer($app)->loadConfiguration($app['config']);
                return $controller;
            }
        );
        $app['tesla_webserverconsole_etc.controller'] = $app->share(
            function () use ($app) {
                $controller = new EtcController($app['twig'], $app['config']->getSetting('tesla-server-console', 'etc'));
                $controller->setContainer($app)->loadConfiguration($app['config']);
                return $controller;
            }
        );
        $app['tesla_webserverconsole_home.controller'] = $app->share(
            function () use ($app) {
                $controller = new HomeController();
                $controller->setContainer($app)->loadConfiguration($app['config']);
                return $controller;
            }
        );
        $app['tesla_webserverconsole_live_dashboard.controller'] = $app->share(
            function () use ($app) {
                $controller = new LiveDashboardController();
                $controller->setContainer($app)->loadConfiguration($app['config']);
                return $controller;
            }
        );
        $app['tesla_webserverconsole_mock.controller'] = $app->share(
            function () use ($app) {
                $controller = new MockController();
                $controller->setContainer($app)->loadConfiguration($app['config']);
                return $controller;
            }
        );

        // factory for poll services
        $app['tesla_webserverconsole_pollitem.factory'] = $app->share(
            function () use ($app) {
                return new PollItemFactory(
                    $app['config']->getSetting('tesla-server-console', 'poll'),
                    $app['url_generator']
                );
            }
        );

        // factory for panels
        $app['tesla_webserverconsole_panel.factory'] = $app->share(
            function () use ($app) {
                return new PanelFactory($app['config']->getSetting(
                    'tesla-server-console',
                    'panels'
                ), $app['tesla_webserverconsole_pollitem.factory']);
            }
        );
        $app['tesla_webserverconsole_panelset.factory'] = $app->share(
            function () use ($app) {
                return new PanelSetFactory($app['config']->getSetting(
                    'tesla-server-console',
                    'panelsets'
                ), $app['tesla_webserverconsole_panel.factory']);
            }
        );

        $app['tesla_server_console_collect_stats.command'] = $app->share(
            function () use ($app) {
                return new CollectStatsCommand($app['tesla_server_console_stats_entry.factory'], $app['config']->getSetting(
                    'tesla-server-console',
                    'stats'
                ));
            }
        );
        $app['tesla_server_console_stats_entry.factory'] = $app->share(
            function () use ($app) {
                return new StatsEntryFactory($app);
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
    public
    function boot(
        Application $app
    ) {

        $this->processCommands(
            $app,
            $app['config']->getSetting(
                'tesla-server-console',
                'commands'
            )
        );

        $app->get(
            '/',
            function (Request $request) use ($app) {
                $controller = $app['tesla_webserverconsole_home.controller'];
                return $controller->indexAction($request);



            }
        )->bind('homepage');

        $app->get(
            'console-config',
            function () use ($app) {
                $controller = new ConsoleConfigController($app['twig']);
                $controller->setContainer($app)->loadConfiguration($app['config']);

                return $controller->listAction($app['config']->getSection('tesla-server-console'));
            }
        )->bind('console-config');



        $app->get(
            '/tesla-server-console/live-dashboard/{panelSet}',
            function ($panelSet) use ($app) {
                $controller = $app['tesla_webserverconsole_live_dashboard.controller'];
                return $controller->getPanelSet($panelSet);

            }
        )->bind('live-dashboard');

        $app->get(
            '/tesla-server-console/php/phpinfo',
            function () use ($app) {
                $controller = $app['tesla_webserverconsole_mock.controller'];
                ob_start();
                phpinfo();
                $doc = ob_get_clean();
                preg_match("/<body[^>]*>(.*?)<\/body>/is", $doc, $matches);
                $html = $matches[1];
                $html = str_replace('<table ', '<table class="table table-striped" ', $html);
                $html = str_replace('class="e"', 'style="width: 30%;font-weight:bold;"', $html);
                $html = "<small>$html</small>";

                return $app['twig']->render('content.html.twig', $controller->extendViewParameters(array('html' => $html)));
            }
        )
            ->bind('php_phpinfo');

        $app->get(
            '/tesla-server-console/php/apc-stat',
            function () use ($app) {
                $controller = $app['tesla_webserverconsole_mock.controller'];
                ob_start();
                require(__DIR__ . '/../Ext/apc_stat.inc.php');
                $html = ob_get_clean();

                return $app['twig']->render('content.html.twig', $controller->extendViewParameters(array('html' => $html)));
            }
        )->bind('php_apc_stat');

        $app->get(
            '/tesla-server-console/memcache/memcache-stat',
            function () use ($app) {
                $controller = $app['tesla_webserverconsole_mock.controller'];
                if (function_exists('memcache_connect')) {

                ob_start();
                require(__DIR__ . '/../Ext/memcache_stat.inc.php');
                $html = ob_get_clean();
                } else {
                    $html = '<h2>memcache not supported on this server</h2>';
                }

                return $app['twig']->render('content.html.twig', $controller->extendViewParameters(array('html' => $html)));
            }
        )->bind('memcache_memcache_stat');

        $app->get(
            '/tesla-server-console/log',
            function (Request $request) use ($app) {
                return $app['tesla_webserverconsole_log.controller']->indexAction($request);
            }
        )->bind('tesla_webserverconsole_log');;

        $app->get(
            '/tesla-server-console/etc',
            function (Request $request) use ($app) {
                return $app['tesla_webserverconsole_etc.controller']->indexAction($request);
            }
        )->bind('tesla_webserverconsole_etc');

        // processes multiple posted json requests
        $app->post(
            '/tesla-server-console/ajax_multicall',
            function (Request $request) use ($app) {
                $items = json_decode($request->getContent());
                $results = new \stdClass();
                $results->success = array();
                $results->failed = array();
                foreach ($items as $item) {
                    $uri = str_replace('/index.php', '', $item->uri);
                    $uri = str_replace('/index_dev.php', '', $uri);
                    $subRequest = Request::create($uri);
                    $response = $app->handle(Request::create($uri), HttpKernelInterface::SUB_REQUEST, false);
                    $data = json_decode($response->getContent());
                    if ($data) {
                        $results->success[$item->id] = $data;
                    } else {
                        $results->failed[$item->id] = $data;
                    }
                }

                return JsonResponse::create($results);
            }
        )->bind('tesla_webserverconsole_ajax_multicall');


    }


} 