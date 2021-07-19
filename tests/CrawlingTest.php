<?php

namespace RenokiCo\Clusteer\Test;

use Illuminate\Support\Str;
use RenokiCo\Clusteer\Clusteer;

class CrawlingTest extends TestCase
{
    public function test_viewport()
    {
        $clusteer = Clusteer::to('http://localhost:8000')
            ->setViewport(1280, 720)
            ->withHtml()
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), '1280x720')
        );
    }

    public function test_device()
    {
        $this->markTestIncomplete(
            'There is no service to check the device yet.'
        );
    }

    public function test_user_agent()
    {
        $clusteer = Clusteer::to('http://localhost:8000')
            ->setUserAgent('Some-Kind-Of-User-Agent')
            ->withHtml()
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'User-Agent: Some-Kind-Of-User-Agent')
        );
    }

    public function test_extra_headers()
    {
        $this->markTestIncomplete(
            'A way to detect extra headers is needed.'
        );

        /* $clusteer = Clusteer::to('https://www.whatismybrowser.com/detect/what-http-headers-is-my-browser-sending')
            ->setExtraHeaders([
                'Some-Kind-Of-Header' => 'Some-Kind-Of-Header-Value',
            ])
            ->withHtml()
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'Some-Kind-Of-Header')
        );

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'Some-Kind-Of-Header-Value')
        ); */
    }

    public function test_block_extensions()
    {
        $clusteer = Clusteer::to('http://localhost:8000')
            ->blockExtensions(['.css'])
            ->waitUntilAllRequestsFinish()
            ->withTriggeredRequests()
            ->get();

        foreach ($clusteer->getTriggeredRequests() as $request) {
            $this->assertFalse(
                (bool) preg_match('/\.css$/', $request['url'])
            );
        }
    }

    public function test_cookies()
    {
        $clusteer = Clusteer::to('http://localhost:8000')
            ->waitUntilAllRequestsFinish()
            ->withCookies()
            ->wait(2)
            ->get();

        $this->assertTrue(
            count($clusteer->getCookies()) > 0
        );
    }

    public function test_console_lines()
    {
        $clusteer = Clusteer::to('http://localhost:8000')
            ->waitUntilAllRequestsFinish()
            ->withConsoleLines()
            ->get();

        $this->assertTrue(
            is_array($clusteer->getConsoleLines()->toArray())
        );
    }

    public function test_screenshot()
    {
        $clusteer = Clusteer::to('https://google.ro')
            ->waitUntilAllRequestsFinish()
            ->withScreenshot()
            ->get();

        $this->assertNotNull(
            $content = $clusteer->getScreenshot()
        );

        file_put_contents('artifacts/test_screenshot.jpeg', $content);
    }
}
