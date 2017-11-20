<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$requestListener = new \Magium\Clairvoyant\RequestListener(
    new \Magium\Clairvoyant\Writer\DirectPost(
        'api.clairvoyant.dev.magiumlib.com', 'user-key', 'user-secret', 'http'
    ),
    'project-1'
);

echo 'Hello';
