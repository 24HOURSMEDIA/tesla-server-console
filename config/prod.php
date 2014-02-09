<?php

// configure your app for the production environment
$app['config'] = $app->share(
    function () {
        $parameterFile = __DIR__ . '/parameters.json';
        $confDir = __DIR__ . '/conf.d';
        $service = new \Tesla\Silex\ConfigurationManager\ConfigurationManager($parameterFile);
        $service->registerConfigFiles(
            array(
                $confDir . '/aws.conf.json',
                $confDir . '/tesla-server-console.conf.json',
                $confDir . '/tesla-user.conf.json',
            )
        );

        return $service;
    }
);