<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 22/05/14
 * Time: 11:55
 */

namespace Tesla\AwsConsoleExtensions\TeslaAwsCloudIntegration\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Tesla\AwsConsoleExtensions\TeslaAwsCloudIntegration\TeslaAwsCloudIntegrationExtension;

class TeslaAwsCloudIntegrationExtensionProvider implements ServiceProviderInterface
{


    public function register(Application $app)
    {

        $app['tesla_server_console.extension.tesla_awscloud_integration'] = $app->share(
            function () use ($app) {
                $extension =new TeslaAwsCloudIntegrationExtension();
                $extension->loadConfiguration($app['config']->getSetting('extensions', 'tesla_awscloud_integration'));
                $extension->setContainer($app);
                return $extension;
            }
        );
    }

    public function boot(Application $app)
    {


    }
} 