<?php

namespace Magium\Clairvoyant\Listener;

use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;

class PhpUnitListener implements TestListener
{

    const TEST_TYPE_PHPUNIT = 'phpunit';

    /**
     * This provides the Clairvoyant-based project ID.  It must be retrieved from the MagiumLib.com website.  If
     * Clairvoyant is enabled and this project ID is missing an exception will be thrown.
     *
     * @var string
     */

    protected $adapter;

    public function __construct(
        $projectId,
        $userKey,
        $userSecret,
        $endpoint = 'ingest.clairvoyant.magiumlib.com',
        GenericClairvoyantAdapter $adapter = null
    )
    {
        if ($adapter === null) {
            $this->adapter = new GenericClairvoyantAdapter(
                self::TEST_TYPE_PHPUNIT,
                $projectId,
                $userKey,
                $userSecret,
                $endpoint
            );
        }
        $this->adapter->reset();
    }

    public function markKeyCheckpoint($checkpoint)
    {
        $this->adapter->markKeyCheckpoint($checkpoint);
    }

    public function addFilter($filter)
    {
        // Ignore - The filter is on the server side
    }

    public function setFormatter($formatter)
    {
        // Ignore -
    }

    public function shutdown()
    {
        $this->adapter->send(); // Final try, just in case (this should never actually send data)
    }

    public function addError(Test $test, Exception $e, $time)
    {
        $this->adapter->addError(
            MagiumListenerAdapterInterface::TEST_RESULT_ERROR,
            $e->getMessage(),
            MagiumListenerAdapterInterface::TYPE_TEST_RESULT,
            MagiumListenerAdapterInterface::TEST_NOTIFICATION_WARNING
        );
    }

    public function addWarning(Test $test, Warning $e, $time)
    {
        $this->adapter->addWarning(
            $e->getMessage(),
            MagiumListenerAdapterInterface::TYPE_NOTIFICATION,
            MagiumListenerAdapterInterface::TEST_RESULT_FAILED
        );
    }

    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
        $testResult = MagiumListenerAdapterInterface::TEST_RESULT_FAILED;
        $this->adapter->addFailure(
            $testResult,
            $e->getMessage(),
            MagiumListenerAdapterInterface::TYPE_TEST_RESULT,
            MagiumListenerAdapterInterface::TEST_RESULT_FAILED
        );
    }

    public function addIncompleteTest(Test $test, Exception $e, $time)
    {
        $testResult = MagiumListenerAdapterInterface::TEST_RESULT_INCOMPLETE;
        $this->adapter->addIncompleteTest(
            $testResult,
            $e->getMessage(),
            MagiumListenerAdapterInterface::TYPE_TEST_RESULT,
            MagiumListenerAdapterInterface::TEST_RESULT_INCOMPLETE
        );
    }

    public function addRiskyTest(Test $test, Exception $e, $time)
    {
        $testResult = MagiumListenerAdapterInterface::TEST_RESULT_RISKY;
        $this->adapter->addRiskyTest(
            $testResult,
            $e->getMessage(),
            MagiumListenerAdapterInterface::TYPE_TEST_RESULT,
            MagiumListenerAdapterInterface::TEST_RESULT_RISKY
        );
    }

    public function addSkippedTest(Test $test, Exception $e, $time)
    {
        $testResult = MagiumListenerAdapterInterface::TEST_RESULT_SKIPPED;
        $this->adapter->addRiskyTest(
            $testResult,
            $e->getMessage(),
            MagiumListenerAdapterInterface::TYPE_TEST_RESULT,
            MagiumListenerAdapterInterface::TEST_RESULT_SKIPPED
        );
    }

    public function startTestSuite(TestSuite $suite)
    {
        $this->adapter->reset();
    }

    public function endTestSuite(TestSuite $suite)
    {
        $this->adapter->send(); // Just in case.
    }

    public function startTest(Test $test)
    {
        $this->adapter->reset();
        $testName = get_class($test);
        $invokedTest = get_class($test);
        if ($test instanceof TestCase) {
            $testName = $test->getName();
            $invokedTest = get_class($test) . '::' . $testName;
        }
        $this->adapter->startTest(
            $testName,
            $invokedTest,
            get_class($test),
            MagiumListenerAdapterInterface::TYPE_TEST_STATUS,
            MagiumListenerAdapterInterface::TEST_STATUS_STARTED
        );
    }

    public function endTest(Test $test, $time)
    {
        $this->adapter->endTest();
    }

    public function setCharacteristic($type, $value)
    {
        $this->adapter->setCharacteristic($type, $value);
    }

}
