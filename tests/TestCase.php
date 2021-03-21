<?php

namespace RenokiCo\Clusteer\Test;

use Orchestra\Testbench\TestCase as Orchestra;
use RenokiCo\Clusteer\ClusteerServer;
use Symfony\Component\Process\Process;

abstract class TestCase extends Orchestra
{
    /**
     * The Clusteer server process.
     *
     * @var \Symfony\Component\Process\Process
     */
    protected $server;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $command = ClusteerServer::create(8080)
            ->nodeJsPath('$(which node)')
            ->jsFilePath('server.js')
            ->configureServer()
            ->buildCommand();

        $this->server = Process::fromShellCommandline($command)
            ->setTimeout(600);

        $process = $this->server->start();

        if (! $process->isRunning()) {
            dd($process->getOutput());
        }

        sleep(2);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $this->server->stop();
    }

    /**
     * {@inheritdoc}
     */
    protected function getPackageProviders($app)
    {
        return [
            \RenokiCo\Clusteer\ClusteerServiceProvider::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'wslxrEFGWY6GfGhvN9L3wH3KSRJQQpBD');
    }
}
