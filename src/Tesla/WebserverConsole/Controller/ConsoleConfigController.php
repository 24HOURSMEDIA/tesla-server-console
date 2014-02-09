<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 11:04
 */

namespace Tesla\WebserverConsole\Controller;


class ConsoleConfigController
{

    private $twig;

    function __construct($twig)
    {
        $this->twig = $twig;
    }

    function listAction($configs)
    {

        return $this->twig->render('TeslaWebserverConsole/ConsoleConfig/list.html.twig', array('configs' => $configs));

    }
} 