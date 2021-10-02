<?php

namespace RenokiCo\Clusteer\Actions;

use RenokiCo\Clusteer\Contracts\Actionable;

class Click extends Action implements Actionable
{
    /**
     * Specify the element to click on.
     *
     * @var string
     */
    protected $selector;

    /**
     * The amount of clicks to make.
     *
     * @var int
     */
    protected $amount = 1;

    /**
     * The button to click on.
     *
     * @var string
     */
    protected $button = 'left';

    /**
     * Initialize the action.
     *
     * @param  string  $selector
     * @param  int  $amount
     * @param  string  $button
     * @return void
     */
    public function __construct(string $selector, int $amount = 1, string $button = 'left')
    {
        $this->selector = $selector;
        $this->amount = $amount;
        $this->button = $button;
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
            'name' => 'click',
            'selector' => $this->selector,
            'amount' => $this->amount,
            'button' => $this->button,
        ];
    }
}
