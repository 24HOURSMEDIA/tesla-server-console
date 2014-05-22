<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 22/05/14
 * Time: 13:10
 */

namespace Tesla\WebserverConsole\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController {


    function indexAction(Request $request) {
        $app = $this->container;
        $panelFactory = $app['tesla_webserverconsole_panel.factory'];
        $panels = array(
            $panelFactory->get('health-summary')
        );

        return $app['twig']->render(
            'index.html.twig',
           $this->extendViewParameters(array(
                'panels' => $panels,
                '_server' => $_SERVER,
                'server_name' => $app['config']->getParameter('console.server_name'),
                'sapi_name' => php_sapi_name(),
                'uname' => php_uname()
            )
           )
        );
    }
} 