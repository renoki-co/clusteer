<?php

namespace RenokiCo\Clusteer\Actions;

use RenokiCo\Clusteer\Contracts\Actionable;

class Wait extends Action implements Actionable
{
    /**
     * The amount of seconds to wait.
     *
     * @var int
     */
    protected $seconds;

    /**
     * Initialize the action.
     *
     * @param  int  $seconds
     * @return void
     */
    public function __construct(int $seconds)
    {
        $this->seconds = $seconds;
    }

    /**
     * Format to an array that can instruct and be read
     * by the JS script to perform a specific action.
     *
     * @return array
     */
    public function format(): array
    {
        return [
            'name' => 'wait',
            'seconds' => $this->seconds,
        ];
    }
}
