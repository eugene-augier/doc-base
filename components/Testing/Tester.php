<?php

namespace PHPDoc\Internal\Testing;

use Exception;
use PHPDoc\Internal\Testing\Loader\TestLoaderInterface;
use ReflectionClass;
use RuntimeException;

class Tester
{
    private TestLoaderInterface $loader;
    private ?string $testMethod = null;

    public function __construct(TestLoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    public function setTestMethod(?string $testMethod): self
    {
        $this->testMethod = $testMethod;
        return $this;
    }

    /**
     * @throws Exception
     * @return array provides information about the test process
     */
    public function test(): array {
        $report = [
            'nb_passed_test' => 0,
            'errors' => [],
            'nb_failed_assertion' => 0,
            'nb_passed_assertion' => 0,
            'nb_skipped' => 0
        ];

        foreach ($this->loader->getResources() as [$realPath, $class]) {
            include_once $realPath;

            if (!class_exists($class)) {
                throw new RuntimeException(sprintf('Class "%s" does not exists', $class));
            }

            $r = new ReflectionClass($class);
            if (!$r->implementsInterface(AssertInterface::class)) {
                throw new RuntimeException(sprintf(
                    'Class "%s" must implements "%s" interface or extends "%s"',
                    $class, AssertInterface::class, Assert::class
                ));
            }

            $instance = $r->newInstanceWithoutConstructor();
            foreach ($r->getMethods() as $method) {
                // start to check parent class methods
                if ($method->class !== $class) {
                    break;
                }

                if ($this->testMethod && !str_starts_with($method->name, $this->testMethod)) {
                    continue;
                }

                // is a skipped test
                if (str_starts_with($method->name, 'skip')) {
                    $report['nb_skipped']++;
                    continue;
                }

                // is not a test method
                if (!str_starts_with($method->name, 'test')) {
                    continue;
                }

                if (!empty($method->getParameters())) {
                    throw new RuntimeException("The test methods must not accept any parameters");
                }

                $method->invoke($instance);

                // we only want register the failures from the current testMethod
                if ($failures = $instance->getLastFailures()) {
                    $report['errors'][] = [
                        'line' => $method->getStartLine(),
                        'file' => $realPath,
                        'test' => $method->name,
                        'failures' => $failures
                    ];
                    $report['nb_failed_assertion'] += count($failures);
                } else {
                    $report['nb_passed_test']++;
                }
            }
            $report['nb_passed_assertion'] += $instance->countPassedAssertions();
        }
        $report['nb_failed_test'] = count($report['errors']);

        return $report;
    }
}
