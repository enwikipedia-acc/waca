<?php
/******************************************************************************
 * Wikipedia Account Creation Assistance tool                                 *
 *                                                                            *
 * All code in this file is released into the public domain by the ACC        *
 * Development Team. Please see team.json for a list of contributors.         *
 ******************************************************************************/

namespace Waca;

use Exception;
use Waca\Exceptions\EnvironmentException;
use Waca\Providers\GlobalState\FakeGlobalStateProvider;
use Waca\Tasks\ConsoleTaskBase;

class ConsoleStart extends ApplicationBase
{
    /**
     * @var ConsoleTaskBase
     */
    private $consoleTask;

    /**
     * ConsoleStart constructor.
     *
     * @param SiteConfiguration $configuration
     * @param ConsoleTaskBase   $consoleTask
     */
    public function __construct(SiteConfiguration $configuration, ConsoleTaskBase $consoleTask)
    {
        parent::__construct($configuration);
        $this->consoleTask = $consoleTask;
    }

    protected function setupEnvironment()
    {
        // initialise super-global providers
        WebRequest::setGlobalStateProvider(new FakeGlobalStateProvider());

        if (WebRequest::method() !== null) {
            throw new EnvironmentException('This is a console task, which cannot be executed via the web.');
        }

        if (Offline::isOffline($this->getConfiguration())) {
            throw new EnvironmentException('The tool is currently disabled.');
        }

        return parent::setupEnvironment();
    }

    protected function cleanupEnvironment()
    {
    }

    /**
     * Main application logic
     */
    protected function main()
    {
        $database = PdoDatabase::getDatabaseConnection($this->getConfiguration());

        $this->setupHelpers($this->consoleTask, $this->getConfiguration(), $database);

        // initialise a database transaction
        if (!$database->beginTransaction()) {
            throw new Exception('Failed to start transaction on primary database.');
        }

        try {
            // run the task
            $this->consoleTask->execute();

            if ($database->hasActiveTransaction()) {
                $database->commit();
            }
        }
        finally {
            // Catch any hanging on transactions
            if ($database->hasActiveTransaction()) {
                $database->rollBack();
            }
        }
    }
}