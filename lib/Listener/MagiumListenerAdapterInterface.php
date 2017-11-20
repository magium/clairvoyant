<?php

namespace Magium\Clairvoyant\Listener;

interface MagiumListenerAdapterInterface
{

    const TYPE_TEST_RESULT = 'test-result';
    const TYPE_TEST_STATUS = 'test-status';
    const TYPE_NOTIFICATION = 'test-notification';
    const TYPE_TEST_CHECKPOINT = 'test-checkpoint';

    const TEST_RESULT_PASSED = 'passed';
    const TEST_RESULT_ERROR = 'error';
    const TEST_RESULT_FAILED = 'failed';
    const TEST_RESULT_SKIPPED = 'skipped';
    const TEST_RESULT_RISKY = 'risky';
    const TEST_RESULT_INCOMPLETE = 'incomplete';

    const TEST_NOTIFICATION_WARNING = 'warning';

    const TEST_STATUS_STARTED = 'started';
    const TEST_STATUS_COMPLETED = 'completed';

}
