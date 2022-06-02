<?php

use PHPDoc\Internal\Parsing\Linter;
use PHPDoc\Internal\Parsing\Tokenizer;
use PHPDoc\Internal\Parsing\Violation;
use PHPDoc\Internal\Testing\Assert;

class LinterTest extends Assert
{
    public const WHITE_SPACE = [
        '^ +',
        'SKIP'
    ];

    public const EOL = [
        '^\n',
        'SKIP'
    ];

    public const SINGLE_LINE_HASH = [
        '^\#.*',
        '"#" must be replaced by "//" for Single-line comments'
    ];

    public const MULTI_LINE_FOR_SINGLE_LINE = [
        '^\/\*(.*)\*\/',
        'Use of multiline comments for Single-line comment is forbidden, it must be replaced by "//"'
    ];

    public function testLint()
    {
        $tokenizer = new Tokenizer();
        $tokenizer->addSkipToken('EOL', '^ +');
        $tokenizer->addSkipToken('WHITE_SPACE', '^\n');

        $linter = new Linter($tokenizer);
        $linter->addRule('no_single_comment_#', self::SINGLE_LINE_HASH[0], self::SINGLE_LINE_HASH[1]);

        $linter->addRule('no_single_comment_/**/', self::MULTI_LINE_FOR_SINGLE_LINE[0], self::MULTI_LINE_FOR_SINGLE_LINE[1]);

        $violations = $linter->lint(<<<'EOD'
<?php
# Comment  # still the same comment /* */
$bar = 'string';
/* Comment /* still the same comment # */
$foo = 1;
/*
 * Valid because not on a single line
 */
$baz = null;
// Valid because use double slash
$boo = true;
EOD
);
        $this->assertCount(2, $violations);

        /** @var Violation $violation */
        $violation = $violations[0];
        $this->assertSame('no_single_comment_#', $violation->getRule());
        $this->assertSame('# Comment  # still the same comment /* */', $violation->getCatch());
        $this->assertSame(self::SINGLE_LINE_HASH[1], $violation->getMessage());
        $this->assertSame(2, $violation->getStartLine());
        $this->assertSame(2, $violation->getEndLine());

        /** @var Violation $violation */
        $violation = $violations[1];
        $this->assertSame('no_single_comment_/**/', $violation->getRule());
        $this->assertSame('/* Comment /* still the same comment # */', $violation->getCatch());
        $this->assertSame(self::MULTI_LINE_FOR_SINGLE_LINE[1], $violation->getMessage());
        $this->assertSame(4, $violation->getStartLine());
        $this->assertSame(4, $violation->getEndLine());
    }
}
