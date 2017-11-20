<?php

namespace Magium\Clairvoyant\Listener;

class MagiumPhpUnit5Listener extends PhpUnit5Listener
{

    const TEST_TYPE_MAGIUM = 'magium';

    public function __construct($projectId, $userKey, $userSecret, $endpoint = 'ingest.clairvoyant.magiumlib.com', GenericClairvoyantAdapter $adapter = null)
    {
        parent::__construct($projectId, $userKey, $userSecret, $endpoint, $adapter);
        $this->adapter->setTestType(self::TEST_TYPE_MAGIUM);
    }

}
