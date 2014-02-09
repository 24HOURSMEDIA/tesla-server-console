<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 19:47
 */

namespace Tesla\WebserverConsole\Stats;


use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tesla\WebserverConsole\Exception\StatsCollectorException;

class StatsEntryFactory
{

    /**
     * @var
     */
    private $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

    function get(array $options)
    {
        $statsEntry = StatsEntry::create();
        $pollList = $this->app['config']->getSetting('tesla-server-console', 'poll');
        $pollDefinition = $pollList[$options['service']];
        if (!$pollDefinition) {
            throw new StatsCollectorException('Poll service definition ' . $options['service'] . ' not found');
        }
        // create an internal route
        $uri = $this->app['url_generator']->generate(
            $pollDefinition['route']['name'],
            $pollDefinition['route']['parameters']
        );
        if (!$uri) {
            throw new StatsCollectorException('Could not resolve route');
        }
        $subRequest = Request::create($uri);
        $response = $this->app->handle($subRequest, HttpKernelInterface::MASTER_REQUEST, false);
        if (!$response->isSuccessful()) {
            throw new StatsCollectorException('Failed response for ' . $uri);
        }
        $pollEntry = json_decode($response->getContent());
        $statsEntry->setValue($pollEntry->value)->setServiceId($options['service']);
        $statsEntry->finish();

        return $statsEntry;
    }
} 