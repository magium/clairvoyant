<?php

namespace Magium\Clairvoyant\Listener;

use Exception;
use PHPUnit\Framework\Test;

class PhpUnit5Listener implements \PHPUnit_Framework_TestListener
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

    public function addError(\PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->adapter->addError(
            MagiumListenerAdapterInterface::TEST_RESULT_ERROR,
            $e->getMessage(),
            MagiumListenerAdapterInterface::TYPE_TEST_RESULT,
            MagiumListenerAdapterInterface::TEST_NOTIFICATION_WARNING
        );
    }

    public function addWarning(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_Warning $e, $time)
    {
        $this->adapter->addWarning(
            $e->getMessage(),
            MagiumListenerAdapterInterface::TYPE_NOTIFICATION,
            MagiumListenerAdapterInterface::TEST_RESULT_FAILED
        );
    }

    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $testResult = MagiumListenerAdapterInterface::TEST_RESULT_FAILED;
        $this->adapter->addFailure(
            $testResult,
            $e->getMessage(),
            MagiumListenerAdapterInterface::TYPE_TEST_RESULT,
            MagiumListenerAdapterInterface::TEST_RESULT_FAILED
        );
    }

    public function addIncompleteTest(\PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $testResult = MagiumListenerAdapterInterface::TEST_RESULT_INCOMPLETE;
        $this->adapter->addIncompleteTest(
            $testResult,
            $e->getMessage(),
            MagiumListenerAdapterInterface::TYPE_TEST_RESULT,
            MagiumListenerAdapterInterface::TEST_RESULT_INCOMPLETE
        );
    }

    public function addRiskyTest(\PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $testResult = MagiumListenerAdapterInterface::TEST_RESULT_RISKY;
        $this->adapter->addRiskyTest(
            $testResult,
            $e->getMessage(),
            MagiumListenerAdapterInterface::TYPE_TEST_RESULT,
            MagiumListenerAdapterInterface::TEST_RESULT_RISKY
        );
    }

    public function addSkippedTest(\PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $testResult = MagiumListenerAdapterInterface::TEST_RESULT_SKIPPED;
        $this->adapter->addRiskyTest(
            $testResult,
            $e->getMessage(),
            MagiumListenerAdapterInterface::TYPE_TEST_RESULT,
            MagiumListenerAdapterInterface::TEST_RESULT_SKIPPED
        );
    }

    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->adapter->reset();
    }

    public function endTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->adapter->send(); // Just in case.
    }

    public function startTest(\PHPUnit_Framework_Test $test)
    {
        $this->adapter->reset();
        $testName = get_class($test);
        $invokedTest = get_class($test);
        if ($test instanceof \PHPUnit_Framework_TestCase) {
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

    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        $this->adapter->endTest();
    }

    public function setCharacteristic($type, $value)
    {
        $this->adapter->setCharacteristic($type, $value);
    }

}
