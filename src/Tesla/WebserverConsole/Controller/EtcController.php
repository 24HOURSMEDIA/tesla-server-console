<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 15:05
 */

namespace Tesla\WebserverConsole\Controller;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

class EtcController
{
    private $twig;

    private $settings;


    function __construct($twig, $settings)
    {
        $this->twig = $twig;
        $this->settings = $settings;
    }

    function indexAction(Request $request)
    {

        $cfg = $this->settings['files'];
        $files = array();


        foreach ($cfg as $c) {

            $finder = new Finder();
            foreach ($finder->files()->in($c['dirs'])->name($c['name'])->ignoreUnreadableDirs(false)->depth(
                         3
                     )->sortByName() as $f) {

                $files['ffd' . md5($f->getFilename())] = $f->getFilename();
            }

        }

        $vars = array();
        if ($request->get('ulimit')) {

            exec('ulimit -aS', $r1);
            exec('ulimit -aH', $r2);
            $vars['ulimit'] = array('soft' => $r1, 'hard' => $r2);


        }

        if ($request->get('sysctl')) {

            exec('sysctl -a', $sysctl);

            $vars['sysctl'] = $sysctl;


        }

        return $this->twig->render('TeslaWebserverConsole/Etc/index.html.twig', $vars);
    }
} 