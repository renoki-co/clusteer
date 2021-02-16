<?php

namespace RenokiCo\Clusteer;

use React\ChildProcess\Process as ReactProcess;
use React\EventLoop\Factory as ReactFactory;

class ClusteerServer
{
    /**
     * The Loop factory.
     *
     * @var \React\EventLoop\Factory
     */
    protected $loop;

    /**
     * The ChildProcess instance.
     *
     * @var \React\ChildProcess\Process
     */
    protected $process;

    /**
     * The arguments to be passed
     * as environment variables to the command.
     *
     * @var array
     */
    protected $env = [];

    /**
     * The JS file path that contains the
     * code to run the server.
     *
     * @var string
     */
    protected $jsFilePath;

    /**
     * The path to the Node.js binary.
     *
     * @var string
     */
    protected $nodeJsPath;

    /**
     * Create a new instance of Clusteer server.
     *
     * @param  int  $port
     * @return ClusteerServer
     */
    public static function create(int $port = 8080)
    {
        return (new static)->setEnv('PORT', $port);
    }

    /**
     * Set an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setEnv(string $key, $value)
    {
        $this->env[$key] = $value;

        return $this;
    }

    /**
     * Set the JS file path.
     *
     * @param  string  $jsFilePath
     * @return $this
     */
    public function jsFilePath(string $jsFilePath)
    {
        $this->jsFilePath = $jsFilePath;

        return $this;
    }

    /**
     * Set the Node.js binary path.
     *
     * @param  string  $nodeJsPath
     * @return $this
     */
    public function nodeJsPath(string $nodeJsPath)
    {
        $this->nodeJsPath = $nodeJsPath;

        return $this;
    }

    /**
     * Configure the Clusteer server's processes and
     * loops to be ran later.
     *
     * @return $this
     */
    public function configureServer()
    {
        ReactProcess::setSigchildEnabled(true);

        $this->loop = ReactFactory::create();

        $command = $this->buildCommand();

        $this->process = new ReactProcess($command);

        $this->process->start($this->loop);

        $this->process->stdout->on('data', function ($chunk) {
            echo $chunk;
        });

        $this->process->stdout->on('error', function ($e) {
            echo $e->getMessage();
        });

        $this->process->on('exit', function ($exitCode, $signal) {
            echo "Clusteer Server failed with exit code {$exitCode}";
        });

        return $this;
    }

    /**
     * Get the Loop factory.
     *
     * @return \React\EventLoop\Factory
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * Get the Process instance.
     *
     * @return \React\ChildProcess\Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * Build the command to run.
     *
     * @return string
     */
    public function buildCommand(): string
    {
        $this->setEnv(
            'CHROMIUM_PATH',
            $this->env['CHROMIUM_PATH'] ?? config('clusteer.chromium_path')
        );

        $params = collect($this->env)
            ->map(function ($value, $key) {
                $value = is_array($value)
                    ? implode(' ', $value)
                    : $value;

                if (in_array($value, ['', null])) {
                    return false;
                }

                $value = escapeshellarg($value);

                return "{$key}={$value}";
            })
            ->filter()
            ->join(' ');

        return "{$params} {$this->nodeJsPath} {$this->jsFilePath}";
    }
}
