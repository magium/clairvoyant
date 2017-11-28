<?php

if (class_exists('Magium\Util\TestCase\RegistrationListener')) {
    $instance = \Magium\Clairvoyant\Registration::getInstance();
    \Magium\Util\TestCase\RegistrationListener::addCallback($instance);
    if (isset($_SERVER['MAGIUM_CLAIRVOYANT_USE_FACTORY'])) {
        $factory = new \Magium\Clairvoyant\MagiumEnvironmentFactory();
        $instance->setAdapter($factory->factory()->getAdapter());
        \Magium\AbstractTestCase::getMasterListener()->addListener($instance->getAdapter()->getListener());
    }
}
