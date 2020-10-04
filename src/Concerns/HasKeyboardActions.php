<?php

namespace RenokiCo\Clusteer\Concerns;

use RenokiCo\Clusteer\Actions\Keyboard;

trait HasKeyboardActions
{
    /**
     * Type a text with a delay between characters.
     *
     * @param  string  $text
     * @param  string  $selector
     * @param  int  $delay
     * @return $this
     */
    public function type(string $text, string $selector, int $delay = 100)
    {
        return $this->action(
            Keyboard::new($text, Keyboard::TYPE, $delay)->on($selector)
        );
    }

    /**
     * Press a key, with the event 'down'.
     *
     * @param  string  $key
     * @return $this
     */
    public function pressDown(string $key)
    {
        return $this->action(Keyboard::new($key, Keyboard::DOWN));
    }

    /**
     * Release a key, with the event 'up'.
     *
     * @param  string  $key
     * @return $this
     */
    public function pressUp(string $key)
    {
        return $this->action(Keyboard::new($key, Keyboard::UP));
    }

    /**
     * Press down a key, then up (mimicking a button press),
     * with a delay between down and up.
     *
     * @param  string  $key
     * @param  int  $delay
     * @return $this
     */
    public function press(string $key, int $delay = 100)
    {
        return $this->action(Keyboard::new($key, Keyboard::PRESS, $delay));
    }
}
