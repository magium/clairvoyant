<?php

namespace Magium\Clairvoyant\Tests;

use PHPUnit\Framework\TestCase;

class ListenerConfigurationTest extends TestCase
{

    public function testListenerInPhpunitXmlConfiguration()
    {
        $a = 1;
        self::assertEquals(1, $a);
    }

    public function testListenerCausesAFailure()
    {
        $a = 1;
        self::assertEquals(1, $a);
    }

}
