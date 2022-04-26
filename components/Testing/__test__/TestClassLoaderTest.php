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
        $loader->loadFile(__DIR__.'/fixtures/a/__special_test__/SampleTest.php');

        [$file, $class] = $loader->getResources()[0];

        $this->assertCount(1, $loader->getResources());
        $this->assertTrue(str_ends_with($file, 'SampleTest.php'));
        $this->assertSame($class, 'SampleTest');
    }

    public function testIsValidPath()
    {
        $loader = $this->createLoader();
        $loader->setTestDirName('foo');

        foreach (['foo', '/foo', 'foo/', '/foo/', 'bar/foo', 'foo/bar'] as $testDir) {
            $this->assertTrue($loader->isValidPath($testDir));
        }

        foreach (['foo/..', 'foo/../', 'foo/../foo/../', 'bar/foo/../foo/../bar'] as $testDir) {
            $this->assertFalse($loader->isValidPath($testDir));
        }

        $loader->excludes(['bar']);
        $this->assertFalse($loader->isValidPath('foo/bar/'));
        $this->assertFalse($loader->isValidPath('exclude'));


        foreach (['foo/bar/..', 'foo/bar/../', 'foo/bar/../bar/../'] as $testDir) {
            $this->assertTrue($loader->isValidPath($testDir));
        }
    }

    public function testFileValidity()
    {
        $loader = $this->createLoader();
        $this->assertFalse($loader->isValidFile(__DIR__.'/fixtures/__special_test__/FakeClassTest.ph'));

        $loader->excludes(['exclude']);
        $this->assertFalse($loader->isValidFile(__DIR__.'/fixtures/__special_test__/exclude/NeverFoundClassTest.php'));
        $this->assertFalse($loader->isValidFile(__DIR__.'/fixtures/a/NotIn__special_test__DirectoryTest.php'));

        $this->assertTrue($loader->isValidFile(__DIR__.'/fixtures/__special_test__/FakeClassTest.php'));
    }

    public function testRelativeFileAccess()
    {
        $loader = $this->createLoader();
        $this->assertTrue(file_exists($foo = __DIR__ . '/fixtures/a/__special_test__/../NotIn_test_DirectoryTest.php'));
        $this->assertTrue(file_exists($bar = __DIR__ . '/fixtures/a/__special_test__/../__special_test__/../NotIn_test_DirectoryTest.php'));
        $this->assertFalse($loader->isValidFile($foo));
        $this->assertFalse($loader->isValidFile($bar));

        $loader->excludes(['exclude']);

        $this->assertTrue(file_exists($foo = __DIR__ . '/fixtures/__special_test__/exclude/../FakeClassTest.php'));
        $this->assertTrue(file_exists($bar = __DIR__ . '/fixtures/__special_test__/exclude/../exclude/../FakeClassTest.php'));
        $this->assertTrue($loader->isValidFile($foo));
        $this->assertTrue($loader->isValidFile($bar));
    }

    private function createLoader(): TestClassLoader
    {
        $loader = new TestClassLoader(__DIR__.'/fixtures', 'Test');
        $loader->setTestDirName('__special_test__');

        return $loader;
    }
}