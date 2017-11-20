<?php

namespace Magium\Clairvoyant\Listener;

use Magium\Clairvoyant\Request;

class GenericListenerAdapter implements MagiumListenerAdapterInterface
{

    protected $testResult;
    protected $userKey;
    protected $userSecret;
    protected $endpoint;
    protected $testRunId;
    protected $characteristics = [];
    protected $events = [];
    protected $testId;
    protected $projectId;
    protected $testDescription;
    protected $testTitle;
    protected $testName;
    protected $invokedTest;
    protected $testType;

    public function __construct(
        $testType,
        $projectId,
        $userKey,
        $userSecret,
        $endpoint = 'ingest.clairvoyant.magiumlib.com'
    )
    {
        $this->testType = $testType;
        $this->projectId = $projectId;
        $this->userKey = $userKey;
        $this->userSecret = $userSecret;
        $this->endpoint = $endpoint;
    }

    public function setTestType($testType)
    {
        $this->testType = $testType;
    }

    public function getTestType()
    {
        return $this->testType;
    }

    public function reset()
    {
        $this->testDescription
            = $this->testName
            = $this->testTitle = null;
        $this->testResult = self::TEST_RESULT_PASSED;
        $this->characteristics = [];
        $this->events = [];
        $this->testId = self::getUniqueId();
    }

    public static function getUniqueId()
    {
        // See https://github.com/ircmaxell/RandomLib/issues/55
        if (function_exists('random_bytes')) {
            $unique = uniqid(substr(bin2hex(random_bytes(64)), 0, 64));

        } else if (function_exists('openssl_random_pseudo_bytes')) {
            $unique = uniqid(openssl_random_pseudo_bytes(64));
        } else {
            $unique = uniqid('', true);
        }
        return $unique;
    }

    protected function writeGeneralTestResult($message, $type, $value, $testResult = null)
    {
        if ($testResult) {
            $this->testResult = $testResult;
        }
        $this->write([
            'message' => $message,
            'extra' => [
                'type' => $type,
                'value' => $value
            ]
        ]);
    }

    public function markKeyCheckpoint($checkpoint)
    {
        $this->write([
            'message' => $checkpoint,
            'extra' => [
                'type' => self::TYPE_TEST_CHECKPOINT,
                'value' => $checkpoint
            ]
        ]);

    }

    public function addError($testResult, $message, $type, $value)
    {
        $this->writeGeneralTestResult($message, $type, $value, $testResult);
    }

    public function addWarning($message, $type, $value)
    {
        $this->writeGeneralTestResult($message, $type, $value);
    }

    public function addFailure($testResult, $message, $type, $value)
    {
        $this->writeGeneralTestResult($message, $type, $value, $testResult);
    }

    public function addIncompleteTest($testResult, $message, $type, $value)
    {
        $this->writeGeneralTestResult($message, $type, $value, $testResult);
    }

    public function addRiskyTest($testResult, $message, $type, $value)
    {
        $this->writeGeneralTestResult($message, $type, $value, $testResult);
    }

    public function addSkippedTest($testResult, $message, $type, $value)
    {
        $this->writeGeneralTestResult($message, $type, $value, $testResult);
    }

    public function startTestSuite()
    {
        $this->reset();
    }

    public function endTestSuite()
    {
        $this->send(); // Just in case.
    }

    public function startTest($testName, $invokedTest, $testClass, $type, $value)
    {
        $this->reset();
        $this->testName = $testName;
        $this->invokedTest = $invokedTest;

        $this->write([
            'message' => 'Test started',
            'extra' => [
                'type' => $type,
                'value' => $value,
                'class' => $testClass,
                'name' => $testName
            ]
        ]);
    }

    public function endTest()
    {
        $this->send();
    }

    public function setCharacteristic($type, $value)
    {
        $this->characteristics[$type] = $value;
    }

    public function write(array $event)
    {
        if (isset($event['extra']['type']) && $event['extra']['type'] == 'characteristic') {
            $this->characteristics[$event['extra']['characteristic']] = $event['extra']['value'];
            return;
        }
        if (isset($event['extra'][self::TYPE_TEST_RESULT])) {
            $this->testResult = $event['extra'][self::TYPE_TEST_RESULT];
        }
        $event['microtime'] = microtime(true);
        $event['unix_timestamp'] = time();
        $this->events[] = $event;
    }

    public function getTestRunId()
    {
        if ($this->testRunId === null) {
            $this->testRunId = self::getUniqueId();
        }
        return $this->testRunId;
    }

    public function send()
    {
        $payload = [
            'type' => $this->testType,
            'title' => $this->testTitle,
            'description' => $this->testDescription,
            'id' => $this->testId,
            'events' => $this->events,
            'version' => '1',
            'project_id' => $this->projectId,
            'invoked_test' => $this->invokedTest,
            'characteristics' => $this->characteristics,
            'test_result' => $this->testResult,
        ];


        $payload['test_run_id'] = self::getTestRunId();

        $request = new Request(
            $this->endpoint,
            $this->userKey,
            $this->userSecret
        );
        $request->push([$payload]);
    }
}
