<?php

namespace Magium\Clairvoyant\Tests;

use PHPUnit\Framework\TestCase;

class Listener5ConfigurationTest extends TestCase
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
