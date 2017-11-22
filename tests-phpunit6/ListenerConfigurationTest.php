<?php

namespace Magium\Clairvoyant\Tests;

use Magium\AbstractTestCase;
use Magium\Clairvoyant\Capture\PageInformation;
use Magium\Clairvoyant\Listener\GenericClairvoyantAdapter;
use Magium\Clairvoyant\Listener\MagiumListenerAdapterInterface;
use Magium\Clairvoyant\Registration;

class ListenerConfigurationTest extends AbstractTestCase
{

    public function testListenerInPhpunitXmlConfiguration()
    {
        $a = 1;
        self::assertEquals(1, $a);
    }

    public function testListenerCausesAFailure()
    {
        $a = 2;
        self::assertEquals(1, $a);
    }

    public function testClairvoyantRegistersWithMagium()
    {
        $adapter = Registration::getInstance()->getAdapter();
        self::assertInstanceOf(GenericClairvoyantAdapter::class, $adapter);
        self::assertSame($adapter, $this->get(MagiumListenerAdapterInterface::class));
        self::assertSame($adapter, $this->get(GenericClairvoyantAdapter::class));
    }

    public function testCapture()
    {
        $this->commandOpen('https://magiumlib.com/');
        $this->get(PageInformation::class)->capture();
        self::assertTrue(true); // These are used for building, not actual unit tests.
    }

    public function testCaptureTiming()
    {
        $this->startTimer();
        $this->commandOpen('https://magiumlib.com/');
        $this->endTimer('Open Home Page');

        $this->startTimer();
        $this->byText('Cheats')->click();
        $this->endTimer('Click Cheats');

    }

}
