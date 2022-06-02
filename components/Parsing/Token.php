<?php

namespace PHPDoc\Internal\Parsing;

class Token
{
    private string $type;
    private string $regex;
    private string $text = '';
    private array $metadata;
    private int $startLine = 1;
    private int $endLine = 1;
    private bool $toSkip;

    public function __construct(string $type, string $regex, array $metadata = [], bool $toSkip = false)
    {
        $this->type = $type;
        $this->regex = $regex;
        $this->metadata = $metadata;
        $this->toSkip = $toSkip;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }

    public function setRegex(string $regex): void
    {
        $this->regex = $regex;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
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
        if ($this->endLine < $this->startLine) {
            $endLine = $this->startLine;
        }

        $this->endLine = $endLine;
    }

    public function toSkip(): bool
    {
        return $this->toSkip;
    }

    public function setToSkip(bool $toSkip): void
    {
        $this->toSkip = $toSkip;
    }
}
