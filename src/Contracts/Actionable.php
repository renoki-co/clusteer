<?php

namespace RenokiCo\Clusteer\Contracts;

interface Actionable
{
    /**
     * Format to an array that can instructm and be read
     * by the JS script to perform a specific action.
     *
     * @return array
     */
    public function format(): array;
}
