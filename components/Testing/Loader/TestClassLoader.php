<?php

namespace PHPDoc\Internal\Testing\Loader;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class TestClassLoader implements TestLoaderInterface
{
    private string $testDirName;
    private array $excludes = [];
    private array $accepted = [];
    private array $resources = [];
    private string $root;
    private string $testClassSuffix;

    public function __construct(string $root, string $testDirName, string $testClassSuffix)
    {
        $this->setRoot($root);
        $this->setTestDirName($testDirName);
        $this->setTestClassSuffix($testClassSuffix);
    }

    /**
     * Set root directory where files will be loaded
     */
    private function setRoot(string $root): void
    {
        $this->root = rtrim(trim($root), '/').'/';
    }

    /**
     * Get directory name where files can be loaded
     */
    public function getTestDirName(): string
    {
        return $this->testDirName;
    }

    /**
     * Set directory name where files can be loaded
     */
    public function setTestDirName(string $dir): void
    {
        $this->testDirName = '/'.trim($dir, '/').'/';
    }

    /**
     * Suffix of loaded files
     */
    private function setTestClassSuffix(string $testClassSuffix): void
    {
        $this->testClassSuffix = trim($testClassSuffix, '/');
        if (!str_ends_with($testClassSuffix, '.php')) {
            $this->testClassSuffix .= '.php';
        }

    }

    /**
     * Will load files only in specified directories under the root dir
     */
    public function onlyIn(array $accepted): void
    {
        foreach ($accepted as $dir) {
            $this->accepted[] = $this->root.trim($dir, '/').'/';
        }
    }

    /**
     * Will load only one file (ignore $root)
     */
    public function loadFile(string $file): void
    {
        $this->accepted = [$file];
    }

    /**
     * All the files under these directories will be ignored
     */
    public function excludes(array $dirs): void
    {
        foreach ($dirs as $dir) {
            $this->excludes[] = '/'.trim($dir, '/').'/';
        }
    }

    /**
     * return loaded files
     */
    public function getResources(): array
    {
        if (empty($this->resources)) {
            $this->load();
        }

        return $this->resources;
    }
    
    public function load(): void
    {
        $this->resources = [];
        if (empty($this->accepted)) {
            $this->accepted[] = $this->root;
        }

        foreach ($this->accepted as $accepted) {
            if ($this->isValidFile($accepted)) {
                $this->addResource($accepted);
                continue;
            }

            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($accepted));
            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                if (!$this->isValidFile($file->getRealPath())) {
                    continue;
                }

                $this->addResource($file->getRealPath());
            }
        }
    }

    public function isValidFile(string $file): bool
    {
        return file_exists($file) && $this->isValidPath($file) && $this->hasValidSuffix($file);
    }

    public function hasValidSuffix(string $file): bool
    {
        if (empty($this->testClassSuffix)) {
            return true;
        }

        return str_ends_with($file, $this->testClassSuffix);
    }

    public function isValidPath(string $dir): bool
    {
        $dir = '/'.trim($dir, '/').'/';

        // important to avoid relative access to invalid directories: '/in-valid/../in-not-valid'
        $dir = str_replace($this->testDirName.'../', '', $dir);
        foreach ($this->excludes as $exclude) {
            // Same but for opposite reasons
            $dir = str_replace($exclude.'..', '', $dir);

            if (str_contains($dir, $exclude)) {
                return false;
            }
        }

        return str_contains($dir, $this->testDirName);
    }

    private function addResource(string $realpath): void
    {
        // get class name and remove .php ext
        $class = substr(basename($realpath), 0, -4);

        $this->resources[] = [$realpath, $class];
    }
}
