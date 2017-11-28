<?php

namespace Magium\Clairvoyant;

use Magium\Clairvoyant\Listener\MagiumListener;
use Magium\Clairvoyant\Listener\MagiumPhpUnit5Listener;

class MagiumEnvironmentFactory
{

    public function factory()
    {
        $key = $_SERVER['MAGIUM_CLAIRVOYANT_KEY'];
        $secret = $_SERVER['MAGIUM_CLAIRVOYANT_SECRET'];
        $project = $_SERVER['MAGIUM_CLAIRVOYANT_PROJECT_ID'];
        $endpoint = isset($_SERVER['MAGIUM_CLAIRVOYANT_ENDPOINT'])?$_SERVER['MAGIUM_CLAIRVOYANT_ENDPOINT']:false;
        if (!$key) {
            throw new \Exception('Missing environment variable MAGIUM_CLAIRVOYANT_KEY');
        }
        if (!$secret ) {
            throw new \Exception('Missing environment variable MAGIUM_CLAIRVOYANT_SECRET');
        }
        if (!$project) {
            throw new \Exception('Missing environment variable MAGIUM_CLAIRVOYANT_PROJECT_ID');
        }
        if (!$endpoint) {
            $endpoint = 'https://api.clairvoyant.magiumlib.com/';
        }
        if (class_exists('PHPUnit_Framework_Assert')) {
            return new MagiumPhpUnit5Listener($project, $key, $secret, $endpoint);
        }
        return new MagiumListener($project, $key, $secret, $endpoint);
    }

}
