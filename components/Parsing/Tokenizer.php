<?php

namespace PHPDoc\Internal\Parsing;

class Tokenizer implements TokenizerInterface
{
    private string $src;
    private int $nbChars;
    private int $cursor = 0;
    private int $line = 1;
    private array $tokens = [];

    public function __construct(string $src = '')
    {
        $this->setSrc($src);
    }

    public function getNextToken(): ?Token
    {
        if ($this->cursor === $this->nbChars) {
            return null;
        }

        /** @var Token $token */
        foreach ($this->getTokens() as $token) {
            if (!preg_match('/'.$token->getRegex().'/', $this->src, $matches)) {
                continue;
            }

            $token->setStartLine($this->line);
            $this->line += substr_count($matches[0], "\n");
            $token->setEndLine($this->line);

            $this->cursor += $offset = strlen($matches[0]);
            $this->src = substr($this->src, $offset, $this->nbChars);

            return $token->toSkip() ? $this->getNextToken() : $token;
        }

        return null;
    }

    public function getSrc(): string
    {
        return $this->src;
    }

    public function setSrc(string $src): void
    {
        $this->src = $src;
        $this->nbChars = strlen($src);
    }

    public function getNbChars(): int
    {
        return $this->nbChars;
    }

    public function getCursor(): int
    {
        return $this->cursor;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function addToken(string $type, string $regex, array $metadata = []): void
    {
        $this->tokens[] = new Token($type, $regex, $metadata);
    }

    public function addSkipToken(string $type, string $regex, array $metadata = []): void
    {
        $this->tokens[] = new Token($type, $regex, $metadata, true);
    }

    public function addCustomToken(Token $token): void
    {
        $this->tokens[] = $token;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }
}
