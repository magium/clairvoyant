<?php

namespace Magium\Clairvoyant\Signer;

use Psr\Http\Message\RequestInterface;

class Signer
{

    protected $key;
    protected $secret;

    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }

    public function sign(RequestInterface $request)
    {
        $nonce = $this->getNonce();
        $payload = $request->getBody()->getContents();
        if (!function_exists('hash_hmac') || (function_exists('hash_hmac') && array_search('sha256', hash_algos()) === false)) {
            $signature = sha1($nonce . $payload . $this->secret);
            $signatureMethod = 'SHA1';
        } else {
            $signature = hash_hmac('sha256',  $nonce . $payload, $this->secret);
            $signatureMethod = 'SHA256';
        }
        $authorization = sprintf(
            'magium key=%s signature=%s signature-method=%s nonce=%s version=2017-10-27',
            $this->key,
            $signature,
            $signatureMethod,
            $nonce
        );
        $request = $request->withHeader('authorization', $authorization);
        return $request;
    }

    public function getNonce()
    {
        $bytes = '';
        if (function_exists('random_bytes')) {
            $bytes = random_bytes(64);
        } else if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(64);
        } else {
            // Worried more about uniqueness than cryptographic security
            while (strlen($bytes) < 64) {
                $bytes .= mt_rand(0, PHP_INT_MAX);
            }
        }
        return uniqid(bin2hex($bytes), true);
    }

}
