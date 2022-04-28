<?php

namespace PHPDoc\Internal\Parsing;

interface LinterInterface
{
    public function lint(string $src): array;
}
