<?php

namespace RenokiCo\Clusteer;

use RenokiCo\Clusteer\Contracts\Actionable;

class Clusteer
{
    use Concerns\HasClickActions;
    use Concerns\HasKeyboardActions;
    use Concerns\HasTimeActions;

    const DESKTOP_DEVICE = 'desktop';

    const TABLET_DEVICE = 'tablet';

    const MOBILE_DEVICE = 'mobile';

    /**
     * Get the parameters sent to Clusteer.
     *
     * @var array
     */
    protected $query = [];

    /**
     * Get the URL to crawl.
     *
     * @var string
     */
    protected $url;

    /**
     * The list actions to perform within the script run.
     *
     * @var array
     */
    protected $actions = [];

    /**
     * Initialize a Clusteer instance with an URL.
     *
     * @param  string  $url
     * @return $this
     */
    public static function to(string $url)
    {
        return (new static)->setUrl($url);
    }

    /**
     * Set the URL address.
     *
     * @param  string  $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set the viewport.
     *
     * @param  int  $width
     * @param  int  $height
     * @return $this
     */
    public function setViewport(int $width, int $height)
    {
        return $this->setParameter('viewport', "{$width}x{$height}");
    }

    /**
     * Set the device.
     *
     * @param  string  $device
     * @return $this
     */
    public function setDevice(string $device)
    {
        return $this->setParameter('device', $device);
    }

    /**
     * Set the user agent. Overwrites the `setDevice` method.
     *
     * @param  string  $userAgent
     * @return $this
     */
    public function setUserAgent(string $userAgent)
    {
        return $this->setParameter('user_agent', $userAgent);
    }

    /**
     * Set the extra headers. They get serialized as JSON.
     *
     * @param  array  $headers
     * @return $this
     */
    public function setExtraHeaders(array $headers)
    {
        return $this->setParameter('extra_headers', json_encode($headers));
    }

    /**
     * Set the extensions to block.
     *
     * @param  array  $extensions
     * @return $this
     */
    public function blockExtensions(array $extensions)
    {
        return $this->setParameter('blocked_extensions', implode(',', $extensions));
    }

    /**
     * Set the resource types to block.
     *
     * @param  array  $types
     * @return $this
     */
    public function blockResourceTypes(array $types)
    {
        return $this->setParameter('blocked_resource_types', implode(',', $types));
    }

    /**
     * Set the timeout.
     *
     * @param  int  $seconds
     * @return $this
     */
    public function timeout(int $seconds)
    {
        return $this->setParameter('timeout', $seconds);
    }

    /**
     * Wait until all the requests get triggered.
     *
     * @return $this
     */
    public function waitUntilAllRequestsFinish()
    {
        return $this->setParameter('until_idle', 1);
    }

    /**
     * Output the triggered requests.
     *
     * @return $this
     */
    public function withTriggeredRequests()
    {
        return $this->setParameter('triggered_requests', 1);
    }

    /**
     * Output the cookies.
     *
     * @return $this
     */
    public function withCookies()
    {
        return $this->setParameter('cookies', 1);
    }

    /**
     * Output the HTML.
     *
     * @return $this
     */
    public function withHtml()
    {
        return $this->setParameter('html', 1);
    }

    /**
     * Output the console lines.
     *
     * @return $this
     */
    public function withConsoleLines()
    {
        return $this->setParameter('console_lines', 1);
    }

    /**
     * Output the screenshot.
     *
     * @param  int  $quality
     * @return $this
     */
    public function withScreenshot(int $quality = 75)
    {
        return $this->setParameter('screenshot', 1)
            ->setParameter('quality', $quality);
    }

    /**
     * Trigger the crawling.
     *
     * @return ClusteerResponse
     */
    public function get(): ClusteerResponse
    {
        $response = json_decode(
            file_get_contents($this->getCallableUrl()), true
        )['data'];

        return new ClusteerResponse($response);
    }

    /**
     * Set a parameter for Clusteer.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setParameter(string $key, $value)
    {
        $this->query[$key] = $value;

        return $this;
    }

    /**
     * Add a new action to the queue.
     *
     * @param  \RenokiCo\Clusteer\Contracts\Actionable  $action
     * @return $this
     */
    public function action(Actionable $action)
    {
        $this->actions[] = $action;

        return $this;
    }

    /**
     * GEt the actions as JSON.
     *
     * @return string
     */
    public function getActionsAsJson()
    {
        return collect($this->actions)->map(function ($action) {
            return $action->format();
        })->toJson();
    }

    /**
     * Get the callable URL.
     *
     * @return string
     */
    protected function getCallableUrl(): string
    {
        // Ensure url is at the end of the query string.
        $this->setParameter('url', $this->url);

        // Add the actions to the query.
        $this->setParameter('actions', $this->getActionsAsJson());

        $endpoint = config('clusteer.endpoint');
        $query = http_build_query($this->query);

        return "{$endpoint}?{$query}";
    }
}
