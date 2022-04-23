<?php

namespace PHPDoc\Internal\Parsing;

class Linter
{
    public const WHITE_SPACE = [
        '^ +',
        'SKIP'
    ];

    public const EOL = [
        '^\n',
        'SKIP'
    ];

    public const STRING = [
        '^\"[^\".]*\"',
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
}
