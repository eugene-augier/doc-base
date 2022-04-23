<?php

use PHPDoc\Internal\Parsing\Token;
use PHPDoc\Internal\Parsing\Tokenizer;
use PHPDoc\Internal\Testing\Assert;

class TokenizerTest extends Assert
{
    const WHITE_SPACE = 'white_spaces';
    const END_OF_LINE = 'end_of_line';
    const STRING = 'string';
    const SINGLE_LINE_HASH = '#_comments_on_single_line';
    const MULTI_LINE_FOR_SINGLE_LINE = 'multiple_line_comments_on_single_line';

    const TOKENS = [
        self::WHITE_SPACE => [
            '^ +',
            'SKIP'
        ],

        self::END_OF_LINE => [
            '^\n',
            'SKIP'
        ],

        self::STRING => [
            '^\"[^\".]*\"',
            'SKIP'
        ],

        self::SINGLE_LINE_HASH => [
            '^\#.*',
            '"#" must be replaced by "//" for Single-line comments'
        ],

        self::MULTI_LINE_FOR_SINGLE_LINE => [
            '^\/\*(.*)\*\/',
            'Use of multiline comments for Single-line comment is forbidden, it must be replaced by "//"'
        ],
    ];

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
        $tokenizer = new Tokenizer('bar');
        $this->assertSame(null, $tokenizer->getNextToken());

        $tokenizer->setSrc(<<<'EOD'
"1
2
3"
EOD);
        $tokenizer->addToken(self::STRING, self::TOKENS[self::STRING][0], ['message' => self::TOKENS[self::STRING][1]]);

        $token = $tokenizer->getNextToken();
        $this->assertSame(self::STRING, $token->getType());
        $this->assertSame(self::TOKENS[self::STRING][0], $token->getRegex());
        $this->assertSame(['message' => self::TOKENS[self::STRING][1]], $token->getMetadata());
        $this->assertFalse($token->toSkip());
        $this->assertSame(1, $token->getStartLine());
        $this->assertSame(3, $token->getEndLine());

        $this->assertNull($tokenizer->getNextToken());
        $this->assertSame($tokenizer->getLine(), $token->getEndLine());
        $this->assertSame($tokenizer->getCursor(), $tokenizer->getNbChars());
        $this->assertEmpty($tokenizer->getSrc(), 'test src is consumed');
    }

    public function testSkipTokens()
    {
        $tokenizer = new Tokenizer(<<<'EOD'
"skipped string" "also skipped" ""
EOD);
        $tokenizer->addSkipToken(self::STRING, self::TOKENS[self::STRING][0], ['message' => self::TOKENS[self::STRING][1]]);
        $tokenizer->addToken(self::WHITE_SPACE, self::TOKENS[self::WHITE_SPACE][0], ['message' => self::TOKENS[self::WHITE_SPACE][1]]);

        $this->assertSame(2, count($tokenizer->getTokens()),'test tokens count');
        $this->assertSame(self::WHITE_SPACE, $tokenizer->getNextToken()->getType(),'test string token is skipped and give white space');
        $this->assertSame(self::WHITE_SPACE, $tokenizer->getNextToken()->getType(),'test string token is skipped and give white space');
        $this->assertNull($tokenizer->getNextToken(),'test no more tokens');
    }

    public function skipThisTest()
    {
    }
}
