<?php

namespace PHPDoc\Internal\Testing\Loader;

interface TestLoaderInterface
{
    public const TEST_DIR_NAME = '/__test__/';

    public function load(): void;

    /**
     * @return array of real path file
     */
    public function getResources(): array;
}
