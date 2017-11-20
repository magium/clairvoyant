<?php

namespace Magium\Clairvoyant\Listener;

class MagiumListener extends PhpUnitListener
{

    const TEST_TYPE_MAGIUM = 'magium';

    public function __construct($projectId, $userKey, $userSecret, $endpoint = 'ingest.clairvoyant.magiumlib.com', GenericClairvoyantAdapter $adapter = null)
    {
        parent::__construct($projectId, $userKey, $userSecret, $endpoint, $adapter);
        $this->adapter->setTestType(self::TEST_TYPE_MAGIUM);
    }

}
