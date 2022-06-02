<?php

namespace PHPDoc\Internal\Parsing\Exception;

use Exception;
use PHPDoc\Internal\Parsing\Tokenizer;
use Throwable;

class UnexpectedTokenException extends Exception
{
    public function __construct(string $token = "", int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf('Unexpected token "%s". Set %s::skipUnknown to true if you want skip all the unknown token', $token, Tokenizer::class);
        parent::__construct($message, $code, $previous);
    }
}
