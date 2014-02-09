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
use Tesla\WebserverConsole\Controller\ConsoleConfigController;
use Tesla\WebserverConsole\Controller\LogController;
use Symfony\Component\HttpFoundation\Request;
use Tesla\WebserverConsole\Exception\WebserverConsoleException;
use Tesla\WebserverConsole\Panel\PanelFactory;
use Tesla\WebserverConsole\Poll\PollItemFactory;
use Tesla\WebserverConsole\Panel\PanelsetFactory;

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
                return new LogController($app['twig']);
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
            'console-config',
            function () use ($app) {
                $controller = new ConsoleConfigController($app['twig']);

                return $controller->listAction($app['config']->getSection('tesla-server-console'));
            }
        )->bind('console-config');

        $app->get(
        '/',
            function () use ($app) {

                $panelFactory = $app['tesla_webserverconsole_panel.factory'];
                $panels = array(
                    $panelFactory->get('health-summary')
                );

                return $app['twig']->render(
                    'index.html.twig',
                    array(
                        'panels' => $panels,
                        '_server' => $_SERVER,
                        'server_name' => $app['config']->getParameter('console.server_name'),
                        'sapi_name' => php_sapi_name(),
                        'uname' => php_uname()
                    )
                );
            }
        )->bind('homepage');

        $app->get(
            '/tesla-server-console/live-dashboard/{panelSet}',
            function ($panelSet) use ($app) {
                try {
                    $panels = $app['tesla_webserverconsole_panelset.factory']->get($panelSet);
                } catch (\Exception $e) {
                    $panels = array();
                }
                $panelSets = $app['config']->getSetting('tesla-server-console', 'panelsets');

                return $app['twig']->render(
                    'live-dashboard.html.twig',
                    array('panels' => $panels, 'panelsets' => $panelSets, 'panelset' => $panelSets[$panelSet])
                );
            }
        )->bind('live-dashboard');

        $app->get(
            '/tesla-server-console/php/phpinfo',
            function () use ($app) {
                ob_start();
                phpinfo();
                $doc = ob_get_clean();
                preg_match("/<body[^>]*>(.*?)<\/body>/is", $doc, $matches);
                $html = $matches[1];
                $html = str_replace('<table ', '<table class="table table-striped" ', $html);
                $html = str_replace('class="e"', 'style="width: 30%;font-weight:bold;"', $html);
                $html = "<small>$html</small>";

                return $app['twig']->render('content.html.twig', array('html' => $html));
            }
        )
            ->bind('php_phpinfo');

        $app->get(
            '/tesla-server-console/php/apc-stat',
            function () use ($app) {
                ob_start();
                require(__DIR__ . '/../Ext/apc_stat.inc.php');
                $html = ob_get_clean();

                return $app['twig']->render('content.html.twig', array('html' => $html));
            }
        )->bind('php_apc_stat');

        $app->get(
            '/tesla-server-console/memcache/memcache-stat',
            function () use ($app) {
                ob_start();
                require(__DIR__ . '/../Ext/memcache_stat.inc.php');
                $html = ob_get_clean();

                return $app['twig']->render('content.html.twig', array('html' => $html));
            }
        )->bind('memcache_memcache_stat');

        $app->get(
            '/tesla-server-console/log',
            function (Request $request) use ($app) {
                return $app['tesla_webserverconsole_log.controller']->indexAction($request);
            }
        )->bind('tesla_webserverconsole_log');;


    }


} 