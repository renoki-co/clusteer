<?php

namespace RenokiCo\Clusteer\Test;

use Illuminate\Support\Str;
use RenokiCo\Clusteer\Clusteer;

class CrawlingTest extends TestCase
{
    public function test_viewport()
    {
        $clusteer = Clusteer::to('https://viewportsizer.com/lite')
            ->setViewport(1280, 720)
            ->withHtml()
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), '<span class="height">720</span>')
        );

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), '<span class="width">1280</span>')
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
        $clusteer = Clusteer::to('https://www.whatismybrowser.com/detect/what-is-my-user-agent')
            ->setUserAgent('Some-Kind-Of-User-Agent')
            ->withHtml()
            ->get();

        $this->assertTrue(
            Str::contains($clusteer->getHtml(), 'Some-Kind-Of-User-Agent')
        );
    }

    public function test_extra_headers()
    {
        $clusteer = Clusteer::to('https://www.whatismybrowser.com/detect/what-http-headers-is-my-browser-sending')
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
        );
    }

    public function test_block_extensions()
    {
        $clusteer = Clusteer::to('https://renoki.org/')
            ->blockExtensions(['.js'])
            ->waitUntilAllRequestsFinish()
            ->withTriggeredRequests()
            ->get();

        foreach ($clusteer->getTriggeredRequests() as $request) {
            $this->assertFalse(
                (bool) preg_match('/\.js$/', $request['url'])
            );
        }
    }

    public function test_cookies()
    {
        $clusteer = Clusteer::to('https://www.whatismybrowser.com/detect/are-cookies-enabled?utm_source=whatismybrowsercom&utm_medium=internal&utm_campaign=detect-index')
            ->waitUntilAllRequestsFinish()
            ->withCookies()
            ->get();

        $this->assertTrue(
            count($clusteer->getCookies()) > 0
        );
    }

    public function test_console_lines()
    {
        $clusteer = Clusteer::to('https://facebook.com')
            ->waitUntilAllRequestsFinish()
            ->withConsoleLines()
            ->get();

        $this->assertTrue(
            count($clusteer->getConsoleLines()) > 0
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
