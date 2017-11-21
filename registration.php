<?php

if (class_exists(\Magium\Util\TestCase\RegistrationListener::class)) {
    \Magium\Util\TestCase\RegistrationListener::addCallback(\Magium\Clairvoyant\Registration::getInstance());
}
