<?php

namespace RenokiCo\Clusteer;

class ClusteerResponse
{
    /**
     * The response.
     *
     * @var array
     */
    protected $response = [];

    /**
     * Initialize the class.
     *
     * @param  array  $response
     * @return void
     */
    public function __construct(array $response = [])
    {
        $this->response = $response;
    }

    /**
     * Get the HTTP status of the crawled page.
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->response['status'];
    }

    /**
     * Get the list of triggered requests.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTriggeredRequests()
    {
        return collect($this->response['triggered_requests']);
    }

    /**
     * Get the list of cookies from the crawled page.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCookies()
    {
        return collect($this->response['cookies']);
    }

    /**
     * Get the HTML content of the crawled page.
     *
     * @return string
     */
    public function getHtml(): string
    {
        return $this->response['html'];
    }
}
