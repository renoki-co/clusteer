<?php

namespace RenokiCo\Clusteer\Actions;

class WaitForSelector extends Wait
{
    /**
     * The selector to wait for.
     *
     * @var string
     */
    protected $selector;

    /**
     * Initialize the action.
     *
     * @param  string  $selector
     * @param  int  $seconds
     * @return void
     */
    public function __construct(string $selector, int $seconds)
    {
        parent::__construct($seconds);

        $this->selector = $selector;
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
            'name' => 'wait-for-selector',
            'selector' => $this->selector,
            'seconds' => $this->seconds,
        ];
    }
}
