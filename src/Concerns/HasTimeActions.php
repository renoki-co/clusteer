<?php

namespace RenokiCo\Clusteer\Concerns;

use RenokiCo\Clusteer\Actions\Wait;
use RenokiCo\Clusteer\Actions\WaitForSelector;

trait HasTimeActions
{
    /**
     * Wait for a certain amount of seconds.
     *
     * @param  int  $seconds
     * @return $this
     */
    public function wait(int $seconds)
    {
        return $this->action(Wait::new($seconds));
    }

    /**
     * Wait for a certain amount of seconds.
     *
     * @param  string  $selector
     * @param  int  $seconds
     * @return $this
     */
    public function waitForSelector(string $selector, int $seconds)
    {
        return $this->action(WaitForSelector::new($selector, $seconds));
    }
}
