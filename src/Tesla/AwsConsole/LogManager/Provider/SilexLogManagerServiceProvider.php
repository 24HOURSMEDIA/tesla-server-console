<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 06/02/14
 * Time: 10:54
 */

namespace Tesla\AwsConsole\LogManager\Provider;


use Silex\Application;
use Silex\ServiceProviderInterface;
use Tesla\AwsConsole\LogManager\Command\LogMoveCommand;

class SilexLogManagerServiceProvider implements ServiceProviderInterface {
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


        $app['tesla_aws_console_logmanager_logmove.command'] = $app->share(
            function () use ($app) {
                return new LogMoveCommand($app['serializer'], $app['validator'], $app['aws']->get('S3'));
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
        // TODO: Implement boot() method.

    }


} 