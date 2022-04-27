<?php

use PHPDoc\Internal\Testing\Assert;
use PHPDoc\Internal\Testing\Loader\TestClassLoader;

class TestClassLoaderTest extends Assert
{
    public function testLoad()
    {
        $loader = $this->createLoader();
        $this->assertCount(4, $loader->getResources());
    }

    public function testExclude()
    {
        $loader = $this->createLoader();
        $loader->excludes(['exclude']);

        $this->assertCount(3, $loader->getResources());
    }

    public function testOnlyIn()
    {
        $loader = $this->createLoader();
        $loader->onlyIn(['a']);

        [$file, $class] = $loader->getResources()[0];

        $this->assertCount(1, $loader->getResources());
        $this->assertTrue(str_ends_with($file, 'SampleTest.php'));
        $this->assertSame($class, 'SampleTest');
    }

    public function testOnly()
    {
        $loader = $this->createLoader();
        $loader->loadFile(new SplFileInfo(__DIR__.'/fixtures/a/__special_test__/SampleTest.php'));

        [$file, $class] = $loader->getResources()[0];

        $this->assertCount(1, $loader->getResources());
        $this->assertTrue(str_ends_with($file, 'SampleTest.php'));
        $this->assertSame($class, 'SampleTest');
    }

    public function testFileValidity()
    {
        $loader = $this->createLoader();
        $this->assertFalse($loader->isValidResource(new SplFileInfo(__DIR__.'/fixtures/__special_test__/FakeClassTest.ph')));

        $loader->excludes(['exclude']);
        $this->assertFalse($loader->isValidResource(new SplFileInfo(__DIR__.'/fixtures/__special_test__/exclude/NeverFoundClassTest.php')));
        $this->assertFalse($loader->isValidResource(new SplFileInfo(__DIR__.'/fixtures/a/NotIn__special_test__DirectoryTest.php')));

        $this->assertTrue($loader->isValidResource(new SplFileInfo(__DIR__.'/fixtures/__special_test__/FakeClassTest.php')));
    }

    public function testRelativeFileAccess()
    {
        $loader = $this->createLoader();
        $this->assertTrue(file_exists($foo = __DIR__ . '/fixtures/a/__special_test__/../NotIn_test_DirectoryTest.php'));
        $this->assertTrue(file_exists($bar = __DIR__ . '/fixtures/a/__special_test__/../__special_test__/../NotIn_test_DirectoryTest.php'));
        $this->assertFalse($loader->isValidResource(new SplFileInfo($foo)));
        $this->assertFalse($loader->isValidResource(new SplFileInfo($bar)));

        $loader->excludes(['exclude']);

        $this->assertTrue(file_exists($foo = __DIR__ . '/fixtures/__special_test__/exclude/../FakeClassTest.php'));
        $this->assertTrue(file_exists($bar = __DIR__ . '/fixtures/__special_test__/exclude/../exclude/../FakeClassTest.php'));
        $this->assertTrue($loader->isValidResource(new SplFileInfo($foo)));
        $this->assertTrue($loader->isValidResource(new SplFileInfo($bar)));
    }

    private function createLoader(): TestClassLoader
    {
        return new TestClassLoader(__DIR__.'/fixtures', '__special_test__', 'Test');
    }
}
