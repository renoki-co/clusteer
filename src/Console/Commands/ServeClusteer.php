<?php

namespace RenokiCo\Clusteer\Console\Commands;

use Illuminate\Console\Command;
use RenokiCo\Clusteer\ClusteerServer;

class ServeClusteer extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $signature = 'clusteer:serve
        {--port=8080 : The port on which the server will run.}
        {--max-browsers=1 : The maximum amount of browsers to run at once.}
        {--chromium-args=* : The arguments to pass to the Chromium browser.}
        {--ignore-https-errors : Wether HTTPS errors should be ignored.}
        {--debug : Enable the debugging.}
        {--default-timeout : The default timeout, in seconds, for any page\'s crawling.}
        {--chromium-path= : The path to the Chromium path.}
        {--node-path=$(which node) : The path to the node executable.}
        {--js-file-path=vendor/renoki-co/clusteer/server.js : The path for the JS file to run.}
        {--show : Display the command that will run instead of running it}
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Launch the Clusteer server.';

    /**
     * The mappings between current options
     * and Node's process.env variables.
     *
     * @var array
     */
    protected static $mappings = [
        'port' => 'PORT',
        'max-browsers' => 'MAX_BROWSERS',
        'chromium-args' => 'CHROMIUM_ARGS',
        'ignore-https-errors' => 'IGNORE_HTTPS_ERRORS',
        'debug' => 'DEBUG',
        'default-timeout' => 'DEFAULT_TIMEOUT',
        'chromium-path' => 'CHROMIUM_PATH',
    ];

    /**
     * Handle the command.
     *
     * @return void
     */
    public function handle()
    {
        pcntl_async_signals(true);

        $server = ClusteerServer::create($this->option('port'));

        foreach (static::$mappings as $consoleKey => $envKey) {
            $server->setEnv($envKey, $this->option($consoleKey));
        }

        $server = $server->jsFilePath($this->option('js-file-path'))
            ->nodeJsPath($this->option('node-path'))
            ->configureServer();

        if ($this->option('show')) {
            return $this->line($server->buildCommand());
        }

        $process = $server->getProcess();
        $loop = $server->getLoop();

        $process->stdout->on('data', function ($chunk) {
            $this->line($chunk);
        });

        // Process the Supervisor's SIGTERM.
        pcntl_signal(SIGTERM, function () use ($process) {
            foreach ($process->pipes as $pipe) {
                $pipe->close();
            }

            $process->stdin->end();

            $process->terminate(SIGTERM);
        });

        // Process the SIGINT coming from manual run.
        pcntl_signal(SIGINT, function () use ($process) {
            foreach ($process->pipes as $pipe) {
                $pipe->close();
            }

            $process->stdin->end();

            $process->terminate(SIGTERM);
        });

        $loop->run();
    }
}
