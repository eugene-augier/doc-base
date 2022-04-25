<?php

namespace PHPDoc\Internal\Testing;

use InvalidArgumentException;
use PHPDoc\Internal\IO\Style;
use PHPDoc\Internal\String\Dumper;
use PHPDoc\Internal\String\Writer;

class Assert implements AssertInterface
{
    private array $failures = [];
    private array $lastFailures = [];
    private int $countPassedAssertions = 0;

    public function countPassedAssertions(): int
    {
        return $this->countPassedAssertions;
    }

    public function getFailures(): array
    {
        return $this->failures;
    }

    public function getLastFailures(): array
    {
        $lastFailures = $this->lastFailures;
        $this->lastFailures = [];

        return $lastFailures;
    }

    protected function assertSame($expected, $given, string $message = ''): void
    {
        $this->assertIs($expected, $given, $message);
    }

    protected function assertNotSame($expected, $given, string $message = ''): void
    {
        $this->assertIsNot($expected, $given, $message);
    }

    protected function assertFalse($given, string $message = ''): void
    {
        $this->assertIs(false, $given, $message);
    }

    protected function assertTrue($given, string $message = ''): void
    {
        $this->assertIs(true, $given, $message);
    }

    protected function assertNull($given, string $message = ''): void
    {
        $this->assertIs(null, $given, $message);
    }

    protected function assertCount($expected, $given, string $message = ''): void
    {
        $this->assertIs($expected, count($given), $message);
    }

    protected function assertEmpty($given, string $message = ''): void
    {
        $this->assertTrue(empty($given), $message);
    }

    protected function assertOfType($expected, $given, string $message = ''): void
    {
        $this->assertIs($expected, gettype($given), $message);
    }

    protected function assertInstanceOf(object $given, $expected, string $message = ''): void
    {
        $this->assert(['instanceof', $expected, $given], 'should be an instance of', $message);
    }

    private function assertIs($expected, $given, string $message = ''): void
    {
        $this->assert(['===', $expected, $given], 'should be', $message);
    }

    private function assertIsNot($expected, $given, string $message = ''): void
    {
        $this->assert(['!==', $expected, $given], 'should not be', $message);
    }

    private function assert($expression, string $reason, string $message): void
    {
        [$operator, $expected, $given] = $expression;
        $pass = match ($operator) {
            '===' => $expected === $given,
            '!==' => $expected !== $given,
            'instanceof' => $given instanceof $expected,
            default => throw new InvalidArgumentException(sprintf('Operator "%s" is not a valid operator.', $operator)),
        };

        $pass
            ? $this->countPassedAssertions++
            : $this->storeFailureMessage($expected, $given, [
                'user_message' => $message,
                'reason' => $reason,
            ]);
    }

    private function storeFailureMessage($expected, $given, array $metadata): void
    {
        $writer = new Writer();
        if ($metadata['user_message']) {
            $writer->writePair('about', $metadata['user_message']);
        }

        $writer->writePair('given', Style::error(Dumper::dump($given)))
            ->writePair($metadata['reason'], Style::success(Dumper::dump($expected)))
            ->cr();

        $this->failures[] = $writer->text();
        $this->lastFailures[] = $writer->text();
    }
}
