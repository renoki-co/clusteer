<?php

namespace RenokiCo\Clusteer\Test;

use Illuminate\Support\Str;
use RenokiCo\Clusteer\Clusteer;

class ClickAndKeyboardTest extends TestCase
{
    public function test_keyboard_type()
    {
        $clusteer = Clusteer::to('http://localhost:8000')
            ->withHtml()
            ->type('this-is-some-test', '#typed-text', 100)
            ->wait(2)
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'Typed text: this-is-some-test')
        );
    }

    public function test_keyboard_press()
    {
        $clusteer = Clusteer::to('http://localhost:8000')
            ->withHtml()
            ->press('Alt')
            ->wait(2)
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'Alt pressed: Yes')
        );
    }

    public function test_keyboard_press_down_and_up()
    {
        $clusteer = Clusteer::to('http://localhost:8000')
            ->withHtml()
            ->pressDown('Alt')
            ->pressUp('Alt')
            ->wait(2)
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'Alt pressed: Yes')
        );
    }

    public function test_clicks()
    {
        $clusteer = Clusteer::to('http://localhost:8000')
            ->withHtml()
            ->leftClick('#button')
            ->rightClick('#button')
            ->middleClick('#button')
            ->wait(2)
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'Right clicked: Yes')
        );

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'Left clicked: Yes')
        );

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'Middle clicked: Yes')
        );
    }
}
