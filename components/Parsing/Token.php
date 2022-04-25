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

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getRegex(): string
    {
        return $this->regex;
    }

    public function setRegex(string $regex): self
    {
        $this->regex = $regex;
        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): Token
    {
        $this->text = $text;
        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getStartLine(): int
    {
        return $this->startLine;
    }

    public function setStartLine(int $startLine): self
    {
        $this->startLine = $startLine;
        return $this;
    }

    public function getEndLine(): int
    {
        return $this->endLine;
    }

    public function setEndLine(int $endLine): self
    {
        $this->endLine = $endLine;
        return $this;
    }

    public function toSkip(): bool
    {
        return $this->toSkip;
    }

    public function setToSkip(bool $toSkip): self
    {
        $this->toSkip = $toSkip;
        return $this;
    }
}
