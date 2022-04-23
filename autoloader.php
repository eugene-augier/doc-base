<?php

$domains = [
    'PHPDoc\\Internal' => '/components'
];

spl_autoload_register(function ($class) use ($domains) {
    foreach ($domains as $namespace => $folder) {
        if (str_starts_with($class, $namespace)) {
            $class = str_replace($namespace, $folder, $class);
        }

        $file = __DIR__.str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';

        if (file_exists($file)) {
            return require $file;
        }
    }

    return false;
});
