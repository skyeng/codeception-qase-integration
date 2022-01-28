<?php

declare(strict_types=1);

namespace Skyeng\Codeception\Qase;

use Codeception\Event\FailEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Extension;
use Codeception\Events;
use Codeception\Test\Cest;
use Codeception\Util\Annotation;

class QaseExtension extends Extension
{
    const ANNOTATION_CASE  = 'qase-case';

    const STATUS_PASSED    = 'passed';
    const STATUS_FAILED    = 'failed';
    const STATUS_SKIPPED   = 'skipped';

    public static $events = [
        Events::SUITE_AFTER     => 'afterSuite',

        Events::TEST_SUCCESS    => 'success',
        Events::TEST_SKIPPED    => 'skipped',
        Events::TEST_FAIL       => 'failed',
        Events::TEST_ERROR      => 'errored',
    ];

    private Client $client;
    private array $results = [];

    public function _initialize()
    {
        if (!isset($this->config['enabled'], $this->config['project'], $this->config['token'])) {
            throw new \Exception('Keys: enabled, project, token must be in the QaseExtension config');
        }

        // If the "enabled" config key will not be resolved. For example, it will be equal to %QASE_ENABLE%
        if (is_string($this->config['enabled']) && strpos($this->config['enabled'], '%') !== false) {
            $this->config['enabled'] = false;
        }

        $this->client = new Client(
            $this->config['project'],
            $this->config['token']
        );
    }

    public function success(TestEvent $event): void
    {
        $test = $event->getTest();
        if (!$test instanceof Cest) {
            return;
        }

        $this->handleResult($test, $this::STATUS_PASSED);
    }

    public function failed(FailEvent $event): void
    {
        $test = $event->getTest();
        if (!$test instanceof Cest) {
            return;
        }

        $this->handleResult($test, $this::STATUS_FAILED);
    }

    public function errored(FailEvent $event): void
    {
        $test = $event->getTest();
        if (!$test instanceof Cest) {
            return;
        }

        $this->handleResult($test, $this::STATUS_FAILED);
    }

    public function skipped(FailEvent $event): void
    {
        $test = $event->getTest();
        if (!$test instanceof Cest) {
            return;
        }

        $this->handleResult($test, $this::STATUS_SKIPPED);
    }

    public function afterSuite(SuiteEvent $event): void
    {
        if ($this->config['enabled'] && $this->results) {
            $this->client->sendResults($this->results);
        }
    }

    private function handleResult(Cest $test, string $status)
    {
        if ($caseId = $this->getCaseId($test)) {
            $this->results[] = [
                'case_id' => $caseId,
                'status' => $status,
            ];
        }
    }

    private function getCaseId(Cest $test): ?int
    {
        if ($caseId = Annotation::forMethod($test->getTestClass(), $test->getTestMethod())->fetch($this::ANNOTATION_CASE)) {
            return (int) $caseId;
        }

        return null;
    }
}
