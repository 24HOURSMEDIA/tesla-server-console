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
use Tesla\WebserverConsole\Controller\LogController;
use Symfony\Component\HttpFoundation\Request;

class SilexWebserverConsoleServiceProvider implements ServiceProviderInterface
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
        $app['tesla_webserverconsole_log.controller'] = $app->share(
            function () use ($app) {
                return new LogController($app['twig']);
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
        $app->get(
            '/',
            function () use ($app) {

                return $app['twig']->render(
                    'index.html.twig',
                    array(
                        '_server' => $_SERVER,
                        'server_name' => $app['config']->getParameter('console.server_name'),
                        'sapi_name' => php_sapi_name(),
                        'uname' => php_uname()
                    )
                );
            }
        )->bind('homepage');

        $app->get(
            '/tesla-server-console/live-dashboard',
            function () use ($app) {
                return $app['twig']->render(
                    'live-dashboard.html.twig',
                    array()
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