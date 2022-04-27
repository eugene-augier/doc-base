<?php

namespace PHPDoc\Internal\Parsing;

interface TokenizerInterface
{
    public function addToken(string $type, string $regex, array $metadata = []): void;

    public function getNextToken(): ?Token;
}
