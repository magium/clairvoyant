<?php

namespace Magium\Clairvoyant;

use Magium\AbstractTestCase;
use Magium\Clairvoyant\Listener\GenericClairvoyantAdapter;
use Magium\Util\TestCase\RegistrationCallbackInterface;

class Registration implements RegistrationCallbackInterface
{
    private $adapter;

    private $addListener;

    private static $self;

    public static function getInstance()
    {
        if (!self::$self instanceof self) {
            self::$self = new self();
        }
        return self::$self;
    }

    public function setAdapter(GenericClairvoyantAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return mixed
     */
    public function getAddListener()
    {
        return $this->addListener;
    }

    /**
     * @param mixed $addListener
     */
    public function setAddListener($addListener)
    {
        $this->addListener = $addListener;
    }

    /**
     * @return GenericClairvoyantAdapter|null
     */

    public function getAdapter()
    {
        return $this->adapter;
    }

    public function register(AbstractTestCase $testCase)
    {
        if ($this->adapter instanceof GenericClairvoyantAdapter) {
            $this->adapter->configureMagium($testCase, $this->addListener);
        }
    }
}
