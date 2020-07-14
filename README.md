Clusteer
========

![CI](https://github.com/renoki-co/clusteer/workflows/CI/badge.svg?branch=master)
[![codecov](https://codecov.io/gh/renoki-co/clusteer/branch/master/graph/badge.svg)](https://codecov.io/gh/renoki-co/clusteer/branch/master)
[![StyleCI](https://github.styleci.io/repos/276691681/shield?branch=master)](https://github.styleci.io/repos/276691681)
[![Latest Stable Version](https://poser.pugx.org/renoki-co/clusteer/v/stable)](https://packagist.org/packages/renoki-co/clusteer)
[![Total Downloads](https://poser.pugx.org/renoki-co/clusteer/downloads)](https://packagist.org/packages/renoki-co/clusteer)
[![Monthly Downloads](https://poser.pugx.org/renoki-co/clusteer/d/monthly)](https://packagist.org/packages/renoki-co/clusteer)
[![License](https://poser.pugx.org/renoki-co/clusteer/license)](https://packagist.org/packages/renoki-co/clusteer)

Clusteer is a Puppeteer wrapper written for Laravel, with the super-power of parallelizing pages across multiple browser instances, thanks to [thomasdondorf/puppeteer-cluster](https://github.com/thomasdondorf/puppeteer-cluster).

This package got inspired from [spatie/browsershot](https://github.com/spatie/browsershot), taking into account the speed of rendering. Instead of opening multiple browsers for each page, Clusteer opens one page for each link, with a maximum amount of browsers you can define via a simple console command.

## 🤝 Supporting

Renoki Co. on GitHub aims on bringing a lot of open source, MIT-licensed projects and helpful projects to the world. Developing and maintaining projects everyday is a harsh work and tho, we love it.

If you are using your application in your day-to-day job, on presentation demos, hobby projects or even school projects, spread some kind words about our work or sponsor our work. Kind words will touch our chakras and vibe, while the sponsorships will keep the open source projects alive.

## 🚀 Installation

You can install the package via composer:

```bash
composer require renoki-co/clusteer
```

## 🙌 Usage

```php
use RenokiCo\Clusteer\Clusteer;

$response = Clusteer::to('https://laravel.com')
    ->waitUntilAllRequestsFinish()
    ->withHtml()
    ->get();

$html = $response->getHtml();
```

## Benchmarks

You may now ask - is it faster than traditional methods that create one browser for each page?

Benchmarks speak for themselves. Below you will find a benchmark that was made on an app that uses a traditional method like [spatie/browsershot](https://github.com/spatie/browsershot) to analyse links, compared to the one used by Clusteer.

### Browsershot

With Browsershot, the CPU usage skyrockets to 99%. AWS forces autoscaling and gets a total of 6 x `m5.large` instances to 99%. There were analysed 100 links every 1 hour. There are only 3 concurrent jobs that process them in parallel. At the end of the benchmark, the analysis was stopped because the CPU usage couldn't get down from 99%.

![Browsershot](benchmarks/1.png "Browsershot")

### Clusteer

With Clusteer, the CPU keeps below 40%. More than that, it was set up to analyse 1000 links every 1 hour, with 3 max opened browsers on 3 concurrent jobs that process them in parallel. In this case, there were only 4 x `m5.large` instances, and they all keep below 40%.

You can see clearly - fewer servers, fewer CPU usage, more analysed links and there is still plenty of room to compute more than this since there is still gap between hours (from :25 to :55 for example)

The advantage here is that there are 3 opened browsers that wait for tasks, so there are no more start-up delays when opening a new browser - only new incognito pages/tabs are created and destroyed after each request.

![Browsershot](benchmarks/2.png "Browsershot")

## Prerequisites for Server

You will need to have a few node packages installed before diving in.

```bash
$ npm install --save dotenv express express-healthcheck puppeteer puppeteer-cluster random-user-agent
```

The server relies on Chromium, so you can find some pretty neat and simple way of getting your Chrome binary file, like [staudenmeir/dusk-updater](https://github.com/staudenmeir/dusk-updater).

Once you managed to get a chromium binary, just create the config file:

```bash
$ php artisan vendor:publish --provider="RenokiCo\Clusteer\ClusteerServiceProvider"
```

Once ready, you can set up in your env the `CLUSTEER_CHROMIUM_PATH` variable and you should avoid specifying it anywhere because it will become the default binary path.

## Starting the Server

You can get a Puppeteer cluster server with a simple `php artisan clusteer:serve`. The command also supports parameters that makes the cluster launch easier.

To view the whole list of args you can pass and configure the server, run:

```bash
$ php artisan clusteer:serve --help
```

## Server with Supervisor

It's recommended to run the server with Supervisor. However, make sure that in case of any interruption, the node process may still be running, blocking the allocated port.

In the Supervisor configuration, make sure to add `killasgroup` and `stopasgroup` as described below:

```ini
[program:clusteer]
process_name=%(program_name)s_%(process_num)02d
command=php artisan clusteer:serve
directory=/path/to/your/project
autostart=true
autorestart=true
killasgroup=true
stopasgroup=true
numprocs=1
redirect_stderr=true
stdout_logfile=/dev/null
```

## Client Usage

The client relies on a Clusteer server. You can get it locally by starting the server or having a remote one. Below you will find some examples.

```php
use RenokiCo\Clusteer\Clusteer;

$clusteer = Clusteer::to('https://example.com')
    ->setViewport(1280, 720)
    ->setDevice(Clusteer::MOBILE_DEVICE)
    ->setExtraHeaders([
        'My-Header' => 'Value',
    ])
    ->blockExtensions(['.css'])
    ->withHtml()
    ->get();
```

By default, Puppeteer tells the browser to not wait for all the requests to be ran. Sometimes, you might want to wait for all of the requests to be ready:

```php
$clusteer = Clusteer::to('https://example.com')
    ->waitUntilAllRequestsFinish()
    ->withHtml()
    ->get();
```

Or if you want to see the triggered requests, call `withTriggeredRequests()`:

```php
$clusteer = Clusteer::to('https://example.com')
    ->waitUntilAllRequestsFinish()
    ->withTriggeredRequests()
    ->get();

$clusteer->getTriggeredRequests();
```

Or, you can get the attached cookies during the rendering:

```php
$clusteer = Clusteer::to('https://example.com')
    ->waitUntilAllRequestsFinish()
    ->withCookies()
    ->get();

$clusteer->getCookies();
```

Debugging the console line can be done with `withConsoleLines()`:

```php
$clusteer = Clusteer::to('https://example.com')
    ->waitUntilAllRequestsFinish()
    ->withConsoleLines()
    ->get();

$clusteer->getConsoleLines();
```

To screenshot a page, just call `withScreenshot()`:

```php
$clusteer = Clusteer::to('https://example.com')
    ->waitUntilAllRequestsFinish()
    ->withScreenshot()
    ->get();

$clusteer->getScreenshot();
```

The image comes in base64 and gets decoded in the package automatically. If you wish to retrieve it as base64, just call `getScreenshot(false)` instead.

You can also set the quality of the screenshot by calling `withScreenshot($quality)`, where `$quality` is a number between `0` and `100`.

## 🐛 Testing

``` bash
vendor/bin/phpunit
```

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒  Security

If you discover any security related issues, please email alex@renoki.org instead of using the issue tracker.

## 🎉 Credits

- [Alex Renoki](https://github.com/rennokki)
- [All Contributors](../../contributors)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
