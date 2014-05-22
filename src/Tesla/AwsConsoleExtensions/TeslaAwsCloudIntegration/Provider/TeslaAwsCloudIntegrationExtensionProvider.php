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

class _stringClosure {
    private $func;
    function __construct($func) {
        $this->func = $func;
    }
    function __toString() {
        $f = $this->func;
       return $f();
    }
}

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



        $tfunc = new _stringClosure(function() use ($app) {
            // override the name parameter in the config

            $ext = $app['tesla_server_console.extension.tesla_awscloud_integration'];
            $self = $ext->getSelf();
            return $self['instance_id'];
        });




            $app['config']->setParameter('console.server_name', $tfunc);//strtoupper($self['instance_id']));



    }
} 