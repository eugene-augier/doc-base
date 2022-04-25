<?php

use PHPDoc\Internal\Parsing\Exception\UnexpectedTokenException;
use PHPDoc\Internal\Parsing\Token;
use PHPDoc\Internal\Parsing\Tokenizer;
use PHPDoc\Internal\Testing\Assert;

class TokenizerTest extends Assert
{
    const STRING_REGEX = '^\"[^\".]*\"';
    const WHITE_SPACE_REGEX = '^ +';
    const CR_REGEX = '^\n';

    public function testSrc()
    {
        $tokenizer = new Tokenizer('foo');
        $this->assertSame('foo', $tokenizer->getSrc());

        $tokenizer->setSrc('bar');
        $this->assertSame('bar', $tokenizer->getSrc());
    }

    public function testHandleToken()
    {
        $tokenizer = new Tokenizer();
        $token = new Token('bar', 'a', ['a' => 'b'], false);

        $tokenizer->addCustomToken($token);
        $tokenizer->addToken('foo', 'b', ['b' => 'c']);
        $tokenizer->addSkipToken('baz', 'c', ['c' => 'd']);
        $tokens = $tokenizer->getTokens();

        $token = $tokens[0];
        $this->assertCount(3, $tokens);
        $this->assertInstanceOf($token, Token::class);
        $this->assertSame('bar', $token->getType());
        $this->assertSame('a', $token->getRegex());
        $this->assertSame(['a' => 'b'], $token->getMetadata());
        $this->assertFalse($token->toSkip());
        $this->assertSame(1, $token->getStartLine());
        $this->assertSame(1, $token->getEndLine());

        $token = $tokens[1];
        $this->assertInstanceOf($token, Token::class);
        $this->assertSame('foo', $token->getType());
        $this->assertSame('b', $token->getRegex());
        $this->assertSame(['b' => 'c'], $token->getMetadata());
        $this->assertFalse($token->toSkip());
        $this->assertSame(1, $token->getStartLine());
        $this->assertSame(1, $token->getEndLine());

        $token = $tokens[2];
        $this->assertInstanceOf($token, Token::class);
        $this->assertSame('baz', $token->getType());
        $this->assertSame('c', $token->getRegex());
        $this->assertSame(['c' => 'd'], $token->getMetadata());
        $this->assertTrue($token->toSkip());
        $this->assertSame(1, $token->getStartLine());
        $this->assertSame(1, $token->getEndLine());
    }

    public function testTokenize()
    {
        $tokenizer = new Tokenizer($src = <<<'EOD'
"1
2
3"
EOD);
        $tokenizer->addToken('string', self::STRING_REGEX, ['foo' => 'bar']);

        $token = $tokenizer->getNextToken();
        $this->assertSame($src, $token->getText());
        $this->assertSame('string', $token->getType());
        $this->assertSame(self::STRING_REGEX, $token->getRegex());
        $this->assertSame(['foo' => 'bar'], $token->getMetadata());
        $this->assertFalse($token->toSkip());

        $this->assertSame(1, $token->getStartLine());
        $this->assertSame(3, $token->getEndLine());

        $this->assertNull($tokenizer->getNextToken());
        $this->assertEmpty($tokenizer->getSrc(), 'test src is consumed');
        $this->assertSame($tokenizer->getLine(), $token->getEndLine());
        $this->assertSame($tokenizer->getCursor(), $tokenizer->getNbChars());

        $tokenizer = new Tokenizer();
        $tokenizer->setSrc("I LOVE PHP");
        $tokenizer->addToken('bar', 'I LOVE');
        $tokenizer->addToken('foo', 'PHP');

        $this->assertSame('I LOVE', $tokenizer->getNextToken()->getText());
        $this->assertSame('PHP', $tokenizer->getNextToken()->getText());
    }

    public function testSkipUnknownByDefault()
    {
        $tokenizer = new Tokenizer(<<<'EOD'
should be skipped
"hello"
EOD);
        $tokenizer->addToken('string', self::STRING_REGEX);

        $token = $tokenizer->getNextToken();
        $this->assertSame('string', $token->getType());
        $this->assertSame('"hello"', $token->getText());
    }

    public function testDontSkipUnknown()
    {
        $tokenizer = new Tokenizer();
        $tokenizer->setSkipUnknown(false);
        $tokenizer->setSrc(<<<'EOD'
$
"hello"
EOD);
        $tokenizer->addToken('string', self::STRING_REGEX);

        try {
            $tokenizer->getNextToken();
        } catch (Exception $e) {
            $this->assertInstanceOf($e, UnexpectedTokenException::class);
            $this->assertSame(
                $e->getMessage(),
                'Unexpected token "$". Set PHPDoc\Internal\Parsing\Tokenizer::skipUnknown to true if you want skip all the unknown token'
            );
        }
    }

    public function testSkipTokens()
    {
        $tokenizer = new Tokenizer('"skipped string" "also skipped" ""');
        $tokenizer->setSkipUnknown(false);
        $tokenizer->addSkipToken('string', self::STRING_REGEX);
        $tokenizer->addToken('white_space', self::WHITE_SPACE_REGEX);

        $token = $tokenizer->getNextToken();
        $this->assertSame(strlen('"skipped string" '), $tokenizer->getCursor());
        $this->assertSame('white_space', $token->getType());
        $this->assertSame(' ', $token->getText());

        $token = $tokenizer->getNextToken();
        $this->assertSame('white_space', $token->getType());
        $this->assertSame(strlen('"skipped string" "also skipped" '), $tokenizer->getCursor());
        $this->assertSame(' ', $token->getText());
        $this->assertNull($tokenizer->getNextToken(),'test no more tokens');
    }

    public function testTokenPriority()
    {
        $tokenizer = new Tokenizer();
        $tokenizer->setSrc(<<<'EOD'

"hello"
EOD);
        $tokenizer->addToken('string', self::STRING_REGEX);
        $tokenizer->addToken('eol', self::CR_REGEX);
        $this->assertSame('eol', $tokenizer->getNextToken()->getType());
    }
}
