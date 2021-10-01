<?php

namespace RenokiCo\Clusteer\Actions;

use RenokiCo\Clusteer\Contracts\Actionable;

class Keyboard extends Action implements Actionable
{
    /**
     * The text to type.
     *
     * @var string
     */
    protected $text;

    /**
     * The selector type on.
     *
     * @var string
     */
    protected $selector;

    /**
     * The mode to act upon.
     * Can be 'type', 'down', 'up', 'press'.
     *
     * @var string
     *
     * @see https://pptr.dev/#?product=Puppeteer&version=v5.3.1&show=api-class-keyboard
     */
    protected $mode = 'type';

    /**
     * The delay between each character.
     *
     * @var int
     */
    protected $delay = 100;

    const TYPE = 'type';

    const DOWN = 'down';

    const UP = 'up';

    const PRESS = 'press';

    /**
     * Initialize the action.
     *
     * @param  string  $text
     * @param  string  $mode
     * @param  int  $delay
     * @return $this
     */
    public function __construct(string $text, string $mode = self::TYPE, int $delay = 100)
    {
        $this->text = $text;
        $this->mode = $mode;
        $this->delay = $delay;
    }

    /**
     * Specify the selector to type in.
     *
     * @param  string  $selector
     * @return $this
     */
    public function on(string $selector)
    {
        $this->selector = $selector;

        return $this;
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
            'name' => $this->mode,
            'text' => $this->text,
            'delay' => $this->delay,
            'selector' => $this->selector,
        ];
    }
}
