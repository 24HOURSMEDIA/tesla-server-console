<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 22/05/14
 * Time: 13:17
 */

namespace Tesla\WebserverConsole\Controller;


class LiveDashboardController extends AbstractController {

    function getPanelSet($panelSet) {
        $app = $this->container;
//try {
$panels = $app['tesla_webserverconsole_panelset.factory']->get($panelSet);
    //} catch (\Exception $e) {
    //    $panels = array();
    //}

$panelSets = $app['config']->getSetting('tesla-server-console', 'panelsets');

return $app['twig']->render(
'live-dashboard.html.twig',
$this->extendViewParameters(array('panels' => $panels, 'panelsets' => $panelSets, 'panelset' => $panelSets[$panelSet]))
);
    }

} 