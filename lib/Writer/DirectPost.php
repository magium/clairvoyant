<?php

namespace Magium\Clairvoyant\Writer;

use Magium\Clairvoyant\Request;

class DirectPost implements WriterInterface
{

    protected $endpoint;
    protected $key;
    protected $secret;
    protected $scheme;

    public function __construct($endpoint, $key, $secret, $scheme = 'https')
    {
        $this->endpoint = $endpoint;
        $this->key = $key;
        $this->secret = $secret;
        $this->scheme = $scheme;
    }

    public function write($id, array $data)
    {
        $request = new Request($this->endpoint, $this->key, $this->secret, $this->scheme);
        $response = $request->push([[
           'id' => $id,
           'data' => $data
        ]]);
        return $response->getBody()->getContents();
    }

}
