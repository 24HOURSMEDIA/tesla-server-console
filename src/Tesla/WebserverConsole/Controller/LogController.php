<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 07/02/14
 * Time: 12:54
 */

namespace Tesla\WebserverConsole\Controller;

use Symfony\Component\Console\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;

class LogController
{


    private $twig;

    function __construct($twig)
    {
        $this->twig = $twig;
    }

    function indexAction(Request $request)
    {
        $count = (int)$request->get('count', 100);
        if ($count > 1000) {
            $count = 1000;
        }
        $fileId = $request->get('file');
        $sort = $request->get('sort', 'desc');





        // get available log files and make a hash key map
        $files = array();
        foreach (Finder::create()->files()->in('/var/log')->name('*.log')->sortByName()->ignoreUnreadableDirs(
                     true
                 ) as $file) {
            /* @var $file \SplFileInfo */
            $key = sha1('weSvdwSw32432432423' . $file->getRealPath());
            try {
                if ($file->getSize() > 0) {
                    $files[$key] = array('id' => $key, 'file' => $file->getRealPath(), 'title' => $file->getFilename());
                }
            } catch (\Exception $e) {

            }
        }


        $selectedFile = null;
        $lines = array();
        if (isset($files[$fileId])) {
            $selectedFile = $files[$fileId];
            $cmd = 'tail ' . escapeshellarg($selectedFile['file']) . ' -n ' . (int)$count;
            $lines = array();
            exec($cmd, $lines);
            if ($sort == 'desc') {
                $lines = array_reverse($lines);
            }
        }

        return $this->twig->render(
            'TeslaWebserverConsole/Log/index.html.twig',
            array(
                'files' => $files,
                'selected_file' => $selectedFile,
                'count' => $count,
                'lines' => $lines,
                'sort' => $sort
            )
        );
    }
} 