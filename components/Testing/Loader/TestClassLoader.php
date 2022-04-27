<?php

namespace PHPDoc\Internal\Testing\Loader;

use PHPDoc\Internal\Loading\FileResolver;
use SplFileInfo;

class TestClassLoader extends FileResolver implements TestLoaderInterface
{
    private string $testDirName;
    private array $accepted = [];
    private array $resources = [];

    public function __construct(string $root, string $testDirName, string $testClassSuffix)
    {
        parent::__construct($root);
        $this->setTestDirName($testDirName);
        $this->setTestClassSuffix($testClassSuffix);
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
        $testClassSuffix = trim($testClassSuffix, '/');
        if (!str_ends_with($testClassSuffix, '.php')) {
            $testClassSuffix .= '.php';
        }

        $this->setSuffix($testClassSuffix);
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
            if ($this->isValidFile(new SplFileInfo($accepted))) {
                $this->addResource($accepted);
                continue;
            }

            $this->setRoot($accepted);
            $this->resolve();
            foreach ($this->getResolved() as $file) {
                if (!$this->isValidResource($file)) {
                    continue;
                }

                $this->addResource($file);
            }
        }
    }

    public function isValidResource(string $file): bool
    {
        $file = str_replace($this->testDirName.'..', '', $file);
        if (!$this->isValidFile(new SplFileInfo($file))) {
            return false;
        }

        return str_contains($file, $this->testDirName);
    }

    private function addResource(string $realpath): void
    {
        // get class name and remove .php ext
        $class = substr(basename($realpath), 0, -4);

        $this->resources[] = [$realpath, $class];
    }
}
