<?php

use PHPDoc\Internal\Parsing\Token;
use PHPDoc\Internal\Testing\Assert;

class TokenTest extends Assert
{
    public function testConstructor()
    {
        $token = new Token('foo', '^bar$', ['foo' => 'bar'], true);
        $this->assertSame('foo', $token->getType());
        $this->assertSame('^bar$', $token->getRegex());
        $this->assertSame(['foo' => 'bar'], $token->getMetadata());
        $this->assertTrue($token->toSkip());
    }

    public function testSetData()
    {
        $token = new Token('foo', '^bar$', ['foo' => 'bar'], true);
        $token->setText('baz');
        $this->assertSame('baz', $token->getText());

        $token->setType('baz');
        $this->assertSame('baz', $token->getType());

        $token->setRegex('baz');
        $this->assertSame('baz', $token->getRegex());

        $token->setMetadata(['baz' => 'foo']);
        $this->assertSame(['baz' => 'foo'], $token->getMetadata());


        $token->setStartLine(3);
        $this->assertSame(3, $token->getStartLine());


        $token->setEndLine(2);
        $this->assertSame(3, $token->getEndLine());

        $token->setToSkip(false);
        $this->assertFalse($token->toSkip());
    }
}
