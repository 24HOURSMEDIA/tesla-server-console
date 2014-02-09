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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


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
        $vars = array();
        $vars['filesets'] = $this->settings['files'];


        if ($fileset = $request->get('fileset')) {

            $files = array();
            $cfg = $this->settings['files'][$fileset];

            $finder = new Finder();

            try {
                foreach ($finder->files()->in($cfg['dirs'])->name($cfg['name'])->ignoreUnreadableDirs(true)->sortByName(
                         ) as $f) {
                    /* @var $f \SplFileInfo */

                    $files['ffd' . md5($f->getRealPath())] = array(
                        'id' => md5($f->getRealPath()),
                        'file' => $f->getRealPath(),
                        'data' => file_get_contents($f)
                    );
                }
                $vars['files'] = $files;
            } catch (\Exception $e) {
                throw new NotFoundHttpException();
            }

        }

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