<?php

namespace Magium\Clairvoyant\Listener;

use Magium\AbstractTestCase;
use Magium\Clairvoyant\Logger\ClairvoyantWriter;
use Magium\Clairvoyant\Registration;
use Magium\Clairvoyant\Request;
use Magium\Util\Log\Logger;

class GenericClairvoyantAdapter implements MagiumListenerAdapterInterface
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
    protected $listener;
    protected $criticalTest = false;

    private $messageHashes = [];

    public function __construct(
        $testType,
        $projectId,
        $userKey,
        $userSecret,
        MagiumPHPUnitListener $listener,
        $endpoint = 'https://ingest.clairvoyant.magiumlib.com/'
    )
    {
        $this->testType = $testType;
        $this->projectId = $projectId;
        $this->userKey = $userKey;
        $this->userSecret = $userSecret;
        $this->endpoint = $endpoint;
        $this->listener = $listener;
        Registration::getInstance()->setAdapter($this);
        register_shutdown_function([$this, 'shutdown']);
    }

    public function getListener()
    {
        return $this->listener;
    }

    public function setTestName($name)
    {
        $this->testName = $name;
    }

    public function setTestDescription($description)
    {
        $this->testDescription = $description;
    }

    public function markTestCritical()
    {
        $this->criticalTest = true;
    }

    public function setTestType($testType)
    {
        $this->testType = $testType;
    }

    public function getTestType()
    {
        return $this->testType;
    }

    public function configureMagium(AbstractTestCase $testCase, $addListener = false)
    {
        $logger = $testCase->getLogger();
        /** @var $logger Logger */
        $this->testRunId = $logger->getTestRunId();
        $writers = $logger->getWriters();
        foreach ($writers as $writer) {
            if ($writer instanceof ClairvoyantWriter) return;
        }
        $logger->addWriter(new ClairvoyantWriter($this));
        $logger->info('Magium Clairvoyant attached logging handler');

        $testCase->setTypePreference(MagiumListenerAdapterInterface::class, GenericClairvoyantAdapter::class);
        $testCase->getDi()->instanceManager()->addSharedInstance($this, MagiumListenerAdapterInterface::class);
        $testCase->getDi()->instanceManager()->addSharedInstance($this, GenericClairvoyantAdapter::class);
        if ($addListener) {
            $testCase->getTestResultObject()->addListener($this->listener);
        }
    }

    public function reset()
    {
        $this->criticalTest = true;
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

    }

    public function startTest($testName, $invokedTest, $testClass, $type, $value)
    {
        /*
         * Because of the send() call here it is possible that if two items (i.e. Clairvoyant and the Magium logger)
         * both call startTest() and the second call triggers a send of the first call.  This has the effect of splitting
         * the test result over two API calls, which is just dumb.
         */
        if ($testName != $this->testName) {
            $this->send();

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
    }

    public function endTest()
    {
        // No send().  We do that at the start of the next test or on __destruct()
    }

    public function shutdown()
    {
        $this->send();
    }

    public function setCharacteristic($type, $value)
    {
        $this->characteristics[$type] = $value;
    }

    public function write(array $event)
    {

        if (!isset($event['priority']) || $event['priority'] > \Zend\Log\Logger::NOTICE) { // NOTICE and below
            return;
        }
        if (isset($event['extra']['type']) && $event['extra']['type'] == 'characteristic') {
            $this->characteristics[$event['extra']['characteristic']] = $event['extra']['value'];
            return;
        }
        if (isset($event['extra'][self::TYPE_TEST_RESULT])) {
            $this->testResult = $event['extra'][self::TYPE_TEST_RESULT];
        }
        // Sometimes messages may come in from multiple sources.  This should stop that

        $message = json_encode($event);
        $hash = sha1($message);
        if (in_array($hash, $this->messageHashes)) {
            return;
        }
        $this->messageHashes[] = $hash;

        $event['microtime'] = microtime(true);
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
        // Nothing has happened
        if (!count($this->events)) return;

        $payload = [
            'type' => $this->testType,
            'title' => $this->testTitle,
            'description' => $this->testDescription,
            'id' => $this->testId,
            'events' => $this->events,
            'project_id' => $this->projectId,
            'invoked_test' => $this->invokedTest,
            'characteristics' => $this->characteristics,
            'test_result' => $this->testResult,
            'critical' => $this->criticalTest
        ];

        $payload['test_run_id'] = $this->getTestRunId();

        $request = new Request(
            $this->endpoint,
            $this->userKey,
            $this->userSecret
        );
        $request->push([$payload]);
        $this->reset();
    }
}
