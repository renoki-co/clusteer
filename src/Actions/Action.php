<?php

namespace RenokiCo\Clusteer\Actions;

use RenokiCo\Clusteer\Contracts\Actionable;

abstract class Action
{
    /**
     * Create a new action.
     *
     * @return \RenokiCo\Clusteer\Contracts\Actionable
     */
    public static function new(): Actionable
    {
        /** @var Actionable $this */
        return new static(...func_get_args());
    }
}
