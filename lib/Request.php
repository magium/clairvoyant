<?php

namespace Magium\Clairvoyant;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Magium\Clairvoyant\Signer\Signer;
use QueryAuth\Credentials\Credentials;
use QueryAuth\Factory;
use QueryAuth\Request\Adapter\Outgoing\GuzzleHttpRequestAdapter;
use QueryAuth\Request\Adapter\Outgoing\GuzzleRequestAdapter;
use QueryAuth\Request\Adapter\Outgoing\GuzzleV6RequestAdapter;

class Request
{

    protected $endpoint;
    protected $key;
    protected $secret;
    protected $scheme = 'https';

    public function __construct($endpoint, $key, $secret)
    {
        $this->endpoint = $endpoint;
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
     * @param $method
     * @param $url
     * @param array|null $payload
     * @return array|Response|null
     */

    protected function doRequest($url, array $payload = null)
    {
            if ($payload) {
                $payload = json_encode($payload);
            }

            $url = $this->scheme . '://' . $this->endpoint . '/' . $url;

            $signer = $this->getSigner();

            $request = new \GuzzleHttp\Psr7\Request('POST', $url, ['content-type' => 'application/json'], $payload);
            $request = $signer->sign($request);

            $guzzle = new Client();
            $response = $guzzle->send($request);

            return $response;
    }

    public function getSigner()
    {
        return new Signer($this->key, $this->secret);
    }

    /**
     * @param Response $response
     * @return mixed
     */

    public function getPayload(Response $response)
    {
        $payload = json_decode($response->getBody(), true);
        return $payload;
    }

    /**
     * @param $url
     * @param array|null $data
     * @return array|Response|null
     */

    public function push(array $data = null)
    {
        return $this->doRequest( 'ingest', $data);
    }

}
