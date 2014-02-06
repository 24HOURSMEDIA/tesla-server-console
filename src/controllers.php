<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    '/live-dashboard',
    function () use ($app) {
        return $app['twig']->render(
            'live-dashboard.html.twig',
            array()
        );
    }
)->bind('live-dashboard');

$app->get(
    '/info/phpinfo',
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
    ->bind('info_phpinfo');


$app->error(
    function (\Exception $e, $code) use ($app) {
        if ($app['debug']) {
            return;
        }

        $page = 404 == $code ? '404.html' : '500.html';

        return new Response($app['twig']->render($page, array('code' => $code)), $code);
    }
);
