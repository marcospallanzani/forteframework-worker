<?php

namespace Forte\Api\Generator\Commands;

use Forte\Api\Generator\Config\ForteApi;
use Forte\Api\Generator\Exceptions\MissingConfigKeyException;
use Forte\Api\Generator\Exceptions\WrongConfigException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Zend\Text\Figlet\Figlet;

/**
 * Class GenerateForteAPI
 *
 * @package Forte\Api\Generator\Commands
 */
class GenerateForteAPI extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'api:generate';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $appConfigFilePath;

    /**
     * GenerateForteAPI constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $appConfigFilePath
     * @param string|null $name
     */
    public function __construct(\Psr\Log\LoggerInterface $logger, $appConfigFilePath, $name = null)
    {
        parent::__construct($name);
        $this->logger = $logger;
        $this->appConfigFilePath = $appConfigFilePath;
    }

    /**
     * Configure the command description.
     */
    protected function configure()
    {
        $this
            ->setDescription('Generates a Forte API from the specified configuration file')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('file', InputArgument::REQUIRED, "The API configuration file (full path)"),
                ])
            );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $figlet = new Figlet(['outputWidth' => 160, 'smushMode' => Figlet::SM_KERN]);
        $output->writeln($figlet->render('FORTE-FRAMEWORK'));
        $output->writeln($figlet->render('API-GENERATION'));

        /******************************************************
         * STEP 1: we read the application configuration file *
         ******************************************************/
        $appConfig = null;
        try {
            // We open general application config file
            $appConfig = Yaml::parseFile($this->appConfigFilePath);

            if (array_key_exists('version', $appConfig)) {
                $initMessage = sprintf(
                    "Initializing ForteFramework API Generator version %s",
                    $appConfig['version']
                );
                $output->writeln($initMessage);
                $this->logger->info($initMessage);
            } else {
                $this->logger->warning(sprintf(
                    "Missing 'version' key in app configuration file '%s'",
                    $this->appConfigFilePath
                ));
            }
        } catch (\Zend\Config\Exception\RuntimeException $exception) {
            $output->writeln(sprintf(
                "Error occurred while reading the application config file '%s'. Check the log files for more info.",
                $this->appConfigFilePath
            ));
            $this->logger->error(sprintf(
                "Error occurred while reading the application config file %s: %s",
                $this->appConfigFilePath,
                $exception->getMessage()
            ));
            $this->logger->error(sprintf(
                "Error trace is: %s",
                $exception->getTraceAsString()
            ));
            exit;
        }

        /****************************************************
         * STEP 2: we read the given API configuration file *
         ****************************************************/
        $configFile = $input->getArgument('file');
        $apiConfig = null;
        try {
            // We open the api config file: this file describes the desired API endpoints and other entities/services
            $initMessage = "Reading given API configuration file '$configFile'";
            $output->writeln($initMessage);
            $this->logger->info($initMessage);

            $apiConfigReader = new \Zend\Config\Reader\Json();
            $apiConfig       = $apiConfigReader->fromFile($configFile);
        } catch (\Zend\Config\Exception\RuntimeException $exception) {
            $output->writeln(sprintf(
                "Error occurred while reading the given API config file '%s'. Check the log files for more info.",
                $configFile
            ));
            $this->logErrorMessageAndTrace($exception);
            exit;
        }

        $forteAPI = null;
        try {
            $forteAPI = new ForteApi($apiConfig);
        } catch (MissingConfigKeyException $missingConfigKeyException) {
            $output->writeln(sprintf(
                "The configuration key '%s' was not found in file '%s'.",
                $missingConfigKeyException->getMissingKey(),
                $configFile
            ));
            $this->logErrorMessageAndTrace($missingConfigKeyException);
        } catch (WrongConfigException $wrongConfigException) {
            $output->writeln(sprintf(
                "The configuration key '%s' in file '%s' is not well configured.",
                $wrongConfigException->getWrongKey(),
                $configFile
            ));
            $this->logErrorMessageAndTrace($wrongConfigException);
        }

        /**************************************************************
         * STEP 3: we copy the skeleton project into the build folder *
         **************************************************************/
//TODO
    }

    /**
     * Log the error message and trace for the given Exception instance.
     *
     * @param \Exception $exception
     */
    protected function logErrorMessageAndTrace(\Exception $exception): void
    {
        $this->logger->error(sprintf(
            "Error message is: %s",
            $exception->getMessage()
        ));
        $this->logger->error(sprintf(
            "Error trace is: %s",
            $exception->getTraceAsString()
        ));
    }
}