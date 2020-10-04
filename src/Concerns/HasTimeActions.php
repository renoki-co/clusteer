<?php

namespace RenokiCo\Clusteer\Concerns;

use RenokiCo\Clusteer\Actions\Wait;

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
}
