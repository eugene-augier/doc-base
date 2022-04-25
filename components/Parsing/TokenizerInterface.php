<?php

namespace PHPDoc\Internal\Parsing;

interface TokenizerInterface
{
    public function getNextToken(): ?Token;
}
