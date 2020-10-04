<?php

namespace RenokiCo\Clusteer\Concerns;

use RenokiCo\Clusteer\Actions\Click;

trait HasClickActions
{
    /**
     * Click a specific element.
     *
     * @param  string  $selector
     * @param  int  $amount
     * @param  string  $button
     * @return $this
     */
    public function click(string $selector, int $amount = 1, string $button = 'left')
    {
        $this->action(Click::new($selector, $amount, $button));

        return $this;
    }

    /**
     * Left click a specific element.
     *
     * @param  string  $selector
     * @param  int  $amount
     * @return $this
     */
    public function leftClick(string $selector, int $amount = 1)
    {
        return $this->click($selector, $amount, 'left');
    }

    /**
     * Right click a specific element.
     *
     * @param  string  $selector
     * @param  int  $amount
     * @return $this
     */
    public function rightClick(string $selector, int $amount = 1)
    {
        return $this->click($selector, $amount, 'right');
    }

    /**
     * Middle click a specific element.
     *
     * @param  string  $selector
     * @param  int  $amount
     * @return $this
     */
    public function middleClick(string $selector, int $amount = 1)
    {
        return $this->click($selector, $amount, 'middle');
    }
}
