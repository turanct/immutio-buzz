<?php

namespace Immutio;

use Buzz\Browser;
use Buzz\Message\RequestInterface;
use Exception;

final class BuzzTransport implements Transport
{
    private $browser;

    public function __construct(Browser $browser)
    {
        $this->browser = $browser;
    }

    /**
     * Issue an HTTP request
     *
     * @param string $method  The http method
     * @param string $url     The url to issue the request to
     * @param string $body    The request body
     * @param array  $headers The request headers
     *
     * @throws RequestFailed when something goes wrong
     *
     * @return Response The response object
     */
    public function request($method, $url, $body, $headers)
    {
        try {
            $response = $this->browser->call($url, $method, $headers, $body);
        } catch (Exception $e) {
            throw new RequestFailed($e->getMessage());
        }

        if ($response->getStatusCode() != 200) {
            throw new RequestFailed($response->getContent());
        }

        $headers = array();
        foreach ($response->getHeaders() as $key => $header) {
            if (strstr($header, 'HTTP/1.1')) {
                continue;
            } elseif (is_int($key)) {
                list($key, $header) = explode(':', $header);
                $headers[trim($key)] = trim($header);
            } else {
                $headers[$key] = $header;
            }
        }

        return new Response(
            $response->getStatusCode(),
            $response->getContent(),
            $headers
        );
    }
}
