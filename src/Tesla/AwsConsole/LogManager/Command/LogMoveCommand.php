<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 06/02/14
 * Time: 11:33
 */

namespace Tesla\AwsConsole\LogManager\Command;

use Aws\S3\S3Client;
use Guzzle\Common\Event;
use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator;
use Tesla\AwsConsole\LogManager\Exception\CommandException;
use Aws\S3\Sync\UploadSyncBuilder;
use Aws\S3\Sync\KeyConverter;
use Aws\S3\Sync\UploadSync;


class LogMoveCommand
{

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var \Symfony\Component\Validator\Validator
     */
    private $validator;

    /**
     * @var S3Client
     */
    private $s3;

    public function __construct(Serializer $serializer, Validator $validator, S3Client $s3)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->s3 = $s3;
    }

    function registerWithConsole(\Symfony\Component\Console\Application $console)
    {
        $_this = $this;
        $console->register('aws:log-move')
            ->setDefinition(
                array(
                    new InputOption('config-dir', null, InputOption::VALUE_REQUIRED, 'The directory containing .conf files'),
                )
            )
            ->setDescription('Moves logs to S3 storage')
            ->setCode(
                function (InputInterface $input, OutputInterface $output) use ($_this) {
                    $_this->execute($input, $output);
                }
            );
    }

    function execute(InputInterface $input, OutputInterface $output)
    {
        $configDir = $input->getOption('config-dir');
        $output->writeln(date('Y-m-d H:i') .  ' Starting Log Move with ' . $configDir);
        if (!$configDir) {
            throw new CommandException('config-dir must be specified');
        }

        // collect configuration files and store validated configuration models in $configs
        $configs = array();
        $output->writeln('Reading configuration files ending in .conf.json from ' . $configDir);
        $fs = new Filesystem();
        $finder = new Finder();
        if (!$fs->exists($configDir) || !is_dir($configDir)) {
            throw new CommandException('config-dir not found');
        }
        $configDir = realpath($configDir);
        $finder->followLinks()->files()->in($configDir)->depth(0)->name('*.conf.json');
        foreach ($finder as $file) {
            /* @var $file \SplFileInfo */
            $contents = file_get_contents($file);
            $config = $this->serializer->deserialize(
                $contents,
                '\Tesla\AwsConsole\LogManager\Model\LogMoveConfigModel',
                'json'
            );
            $errors = $this->validator->validate($config);
            if ($errors->count()) {
                $output->writeln('<error>Skipping ' . $file->getRealPath() . ' because of validation errors</error>');
                foreach ($errors as $error) {
                    /* @var $error  \Symfony\Component\Validator\ConstraintViolation */
                    $output->writeln(
                        '<error>- ' . $error->getPropertyPath() . ': ' . $error->getMessage() . '</error>'
                    );
                }
            } else {
                $output->writeln('<info>- Read ' . $file->getRealPath());
                $configs[] = $config;
            }
        }
        $output->writeln('');
        $output->writeln('Processing log files');

        foreach ($configs as $config) {
            /* @var $config \Tesla\AwsConsole\LogManager\Model\LogMoveConfigModel */
            if ($fs->exists($config->getDir()) && is_dir($config->getDir())) {
                $sourceDir = realpath($config->getDir());
                $sourceConverter = new KeyConverter($sourceDir, $config->getS3Dir());
                $output->writeln('- processing directory ' . $sourceDir . ':');

                $fileList = array();
                $sync = UploadSyncBuilder::getInstance()
                    ->setClient($this->s3)
                    ->setBucket($config->getS3Bucket())
                    ->setAcl($config->getS3Acl())
                    ->setSourceFilenameConverter($sourceConverter)
                    ->uploadFromGlob($sourceDir . '/' . $config->getName())
                    ->force(true)
                    ->build();
                /* @var $sync UploadSync */
                $dispatcher = $sync->getEventDispatcher();
                $beforeTransferListener = function (Event $e) use ($output, &$fileList) {

                    $output->writeln('  transferring ' . $e['file']);
                    $fileList[] = $e['file'];
                    $e->offsetSet('a','b');
                };
                $dispatcher->addListener(UploadSync::BEFORE_TRANSFER,$beforeTransferListener);
                $sync->transfer();
                $dispatcher->removeListener(UploadSync::BEFORE_TRANSFER,$beforeTransferListener);
                if (!count($fileList)) {
                    $output->writeln('  no files found');
                }
                if (count($fileList) && $config->getDeleteAfterTransfer()) {
                    $output->writeln('  removing transferred files');
                    $fs->remove($fileList);
                }
            } else {
                $output->writeln(
                    '<error>Skipping ' . $config->getDir() . ' because it does not exist or is not a directory</error>'
                );
            }
        }
        $output->writeln(date('Y-m-d H:i') .  ' End log move');
    }
} 