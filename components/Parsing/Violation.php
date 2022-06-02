<?php

namespace PHPDoc\Internal\Parsing;

class Violation
{
    private string $rule;
    private string $catch;
    private string $message;
    private int $startLine;
    private int $endLine;

    public function __construct(string $rule, string $catch, string $message, int $startLine, int $endLine)
    {
        $this->rule = $rule;
        $this->catch = $catch;
        $this->message = $message;
        $this->startLine = $startLine;
        $this->endLine = $endLine;
    }

    public function getRule(): string
    {
        return $this->rule;
    }

    public function setRule(string $rule): void
    {
        $this->rule = $rule;
    }

    public function getCatch(): string
    {
        return $this->catch;
    }

    public function setCatch(string $catch): void
    {
        $this->catch = $catch;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getStartLine(): int
    {
        return $this->startLine;
    }

    public function setStartLine(int $startLine): void
    {
        $this->startLine = $startLine;
    }

    public function getEndLine(): int
    {
        return $this->endLine;
    }

    public function setEndLine(int $endLine): void
    {
        $this->endLine = $endLine;
    }

}
