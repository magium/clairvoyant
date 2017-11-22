<?php

namespace Magium\Clairvoyant\Logger;

use Magium\Clairvoyant\Listener\GenericClairvoyantAdapter;
use Magium\Clairvoyant\Listener\MagiumListenerAdapterInterface;
use Zend\Log\Writer\WriterInterface;

class ClairvoyantWriter implements WriterInterface
{

    private $adapter;

    public function __construct(GenericClairvoyantAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function addFilter($filter)
    {

    }

    public function setFormatter($formatter)
    {

    }

    public function write(array $event)
    {
        if (!isset($event['extra']['type'])) {
            $event['extra']['type'] = MagiumListenerAdapterInterface::TYPE_LOG;
        }
        $this->adapter->write($event);
    }

    public function shutdown()
    {
        $this->adapter->send();
    }


}
