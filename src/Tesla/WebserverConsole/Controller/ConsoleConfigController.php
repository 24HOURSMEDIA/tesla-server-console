<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 11:04
 */

namespace Tesla\WebserverConsole\Controller;


class ConsoleConfigController extends AbstractController
{

    private $twig;

    function __construct($twig)
    {
        $this->twig = $twig;
    }

    function listAction($configs)
    {

        return $this->twig->render('TeslaWebserverConsole/ConsoleConfig/list.html.twig', $this->extendViewParameters(array('configs' => $configs)));

    }
} 