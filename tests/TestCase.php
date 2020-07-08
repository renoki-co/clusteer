<?php

namespace RenokiCo\Clusteer\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use RenokiCo\Clusteer\ClusteerServer;
use Symfony\Component\Process\Process;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->startServer();
    }

    /**
     * Get the package providers.
     *
     * @param  mixed  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            \RenokiCo\Clusteer\ClusteerServiceProvider::class,
        ];
    }

    /**
     * Set up the environment.
     *
     * @param  mixed  $app
     * @return void
     */
    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'wslxrEFGWY6GfGhvN9L3wH3KSRJQQpBD');
    }

    /**
     * Start the node server in the background.
     *
     * @return void
     */
    protected function startServer(): void
    {
        $command = ClusteerServer::create(8080)
            ->nodeJsPath('$(which node)')
            ->jsFilePath('server.js')
            ->configureServer()
            ->buildCommand();

        $process = Process::fromShellCommandline($command)
            ->setTimeout(600);

        $process->start();

        sleep(5);
    }
}
