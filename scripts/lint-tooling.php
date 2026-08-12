<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$paths = [
    $root . DIRECTORY_SEPARATOR . 'bin',
    $root . DIRECTORY_SEPARATOR . 'scripts',
];

$files = [];

foreach ($paths as $path) {
    if (!is_dir($path)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $filename = $file->getFilename();
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($extension === 'php' || $extension === '') {
            $files[] = $file->getPathname();
        }
    }
}

sort($files);

if ($files === []) {
    fwrite(STDERR, "No tooling PHP files were found.\n");
    exit(1);
}

foreach ($files as $file) {
    $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file);
    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, "PHP syntax check failed: {$file}\n");
        foreach ($output as $line) {
            fwrite(STDERR, $line . "\n");
        }
        exit($exitCode);
    }

    fwrite(STDOUT, 'OK ' . str_replace($root . DIRECTORY_SEPARATOR, '', $file) . "\n");
    $output = [];
}

fwrite(STDOUT, "Tooling PHP syntax check passed.\n");
