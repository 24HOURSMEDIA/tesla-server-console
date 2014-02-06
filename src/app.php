<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Aws\Common\Aws;

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new \Silex\Provider\SerializerServiceProvider());
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(__DIR__.'/../templates'),
    'twig.options' => array('cache' => __DIR__.'/../cache/twig'),
));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
}));

// register amazon web services
$app['aws'] = $app->share(function() use ($app) {
    $awsCfg = (array)$app['config']->getSection('aws-default');
    $aws = Aws::factory($awsCfg);
    return $aws;
});





$app->register(new \Tesla\AwsConsole\LogManager\Provider\SilexLogManagerServiceProvider());

return $app;
