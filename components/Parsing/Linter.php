<?php

namespace PHPDoc\Internal\Parsing;

class Linter implements LinterInterface
{
    private TokenizerInterface $tokenizer;

    public function __construct(TokenizerInterface $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    public function addRule(string $name, string $regex, string $message): void
    {
        $this->tokenizer->addToken($name, $regex, ['message' => $message]);
    }

    public function lint(string $src): array
    {
        $this->tokenizer->setSrc($src);

        $violations = [];
        while ($token = $this->tokenizer->getNextToken()) {
            $violations[] = new Violation(
                $token->getType(),
                $token->getText(),
                $token->getMetadata()['message'],
                $token->getStartLine(),
                $token->getEndLine()
            );
        }

        return $violations;
    }
}
