<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 09/02/14
 * Time: 19:40
 */

namespace Tesla\WebserverConsole\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Tesla\WebserverConsole\Exception\StatsCollectorException;
use Tesla\WebserverConsole\Stats\StatsEntryFactory;

class CollectStatsCommand
{

    private $entryFactory;

    private $config;

    function __construct(StatsEntryFactory $entryFactory, array $config)
    {
        $this->entryFactory = $entryFactory;
        $this->config = $config;
    }

    function registerWithConsole(\Symfony\Component\Console\Application $console)
    {
        $_this = $this;
        $console->register('tesla:server-console:collect-stats')
            ->setDefinition(
                array()
            )
            ->setDescription('Collect stats (run every minute)')
            ->setCode(
                function (InputInterface $input, OutputInterface $output) use ($_this) {
                    $_this->execute($input, $output);
                }
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return \Tesla\WebserverConsole\Stats\StatsEntry
     */
    function execute(InputInterface $input, OutputInterface $output)
    {

        $fs = new Filesystem();

        $output->writeln('Collect statistics');
        if (!$this->config['enabled']) {
            $output->writeln('disabled by configuration');

            return;

        }
        foreach ($this->config['polls'] as $key => $settings) {

            if (!$settings['enabled']) {
                $output->writeln('- skipping ' . $key . ' because it is disabled');
            } else {
                try {

                    $output->writeln('- collecting ' . $key . '');
                    $path = $this->config['datadir'] . '/' . $this->config['prefix'] . $key . $this->config['suffix'];
                    $fs->mkdir($this->config['datadir']);
                    $output->writeln('- writing result to file ' . $path);
                    $entry = $this->entryFactory->get($settings);
                    $f = fopen($path, 'a+');
                    if (!$f) {
                        throw new StatsCollectorException('Could not open file for writing');
                    }
                    $line = $entry->getTime() . ',' . $entry->getValue() . ',' . $entry->getCalcTime();
                    fputs($f, $line . "\n");
                    $output->writeln('- write ' . $line);
                    fclose($f);
                } catch (\Exception $e) {
                    $output->writeln('<red>- ERROR ' . $e->getMessage() . '</red>');
                }
            }

        }
    }

} 