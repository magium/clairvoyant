<?php

namespace Magium\Clairvoyant\Capture;

use Facebook\WebDriver\WebDriver;
use Magium\Clairvoyant\Listener\MagiumListenerAdapterInterface;
use Zend\Log\Logger;

class PageInformation
{

    protected $webDriver;
    protected $adapter;

    public function __construct(
        WebDriver $webDriver,
        MagiumListenerAdapterInterface $adapter
    )
    {
        $this->webDriver = $webDriver;
        $this->adapter = $adapter;
    }

    public function capture()
    {
        $currentUrl = $this->webDriver->getCurrentURL();
        $title = $this->webDriver->getTitle();
        $windowHandles = $this->webDriver->getWindowHandles();
        $performance = $this->webDriver->executeScript('return performance');
        $entries = $this->webDriver->executeScript('return performance.getEntries()');
        $this->adapter->write([
            'priority' => Logger::NOTICE,
            'extra' => [
                'type' => MagiumListenerAdapterInterface::TYPE_PAGE_INFORMATION,
                'performance' => $performance,
                'current_url' => $currentUrl,
                'title' => $title,
                'entries' => $entries,
                'handles' => $windowHandles
            ]
        ]);
    }

}
