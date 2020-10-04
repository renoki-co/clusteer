<?php

namespace RenokiCo\Clusteer\Test;

use Illuminate\Support\Str;
use RenokiCo\Clusteer\Clusteer;

class ClickAndKeyboardTest extends TestCase
{
    public function test_keyboard_type()
    {
        $clusteer = Clusteer::to('https://inputtypes.com/')
            ->withHtml()
            ->type('this-is-some-test', 'input[type="text"]', 100)
            ->leftClick('button[type="submit"]')
            ->wait(2)
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'value="this-is-some-test"')
        );

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'Form submitted!')
        );
    }

    public function test_keyboard_press()
    {
        $clusteer = Clusteer::to('https://www.keyboardtester.com/tester.html')
            ->withHtml()
            ->press('Alt')
            ->wait(2)
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'key_pressed_m">Alt')
        );
    }

    public function test_keyboard_press_down_and_up()
    {
        $clusteer = Clusteer::to('https://www.keyboardtester.com/tester.html')
            ->withHtml()
            ->pressDown('Alt')
            ->pressUp('Alt')
            ->wait(2)
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'key_pressed_m">Alt')
        );
    }

    public function test_clicks()
    {
        $clusteer = Clusteer::to('https://keyboardtester.co/mouse-click-tester')
            ->withHtml()
            ->leftClick('td[class="mouse-1"]')
            ->rightClick('td[class="mouse-3"]')
            ->middleClick('td[class="mouse-2"]')
            ->wait(2)
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'mouse-1 clicked')
        );

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'mouse-2 clicked')
        );

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'mouse-3 clicked')
        );
    }
}
