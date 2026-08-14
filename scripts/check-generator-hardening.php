<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$temp = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'wp24h-generator-hardening-' . bin2hex(random_bytes(4));

try {
    testModuleGenerator($root, $temp);

    if (is_file($root . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wp24h-init')) {
        testScaffolder($root, $temp);
    }

    fwrite(STDOUT, "Generator hardening checks passed.\n");
} finally {
    removeTree($temp);
}

function testModuleGenerator(string $root, string $temp): void
{
    $project = $temp . DIRECTORY_SEPARATOR . 'module-project';
    mkdir($project . DIRECTORY_SEPARATOR . 'bin', 0775, true);

    copy(
        $root . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wp24h-make-module',
        $project . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wp24h-make-module'
    );

    file_put_contents(
        $project . DIRECTORY_SEPARATOR . 'composer.json',
        json_encode(
            [
                'autoload' => [
                    'psr-4' => [
                        'Smoke\\Plugin\\' => 'src/',
                    ],
                ],
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ) . "\n"
    );

    file_put_contents(
        $project . DIRECTORY_SEPARATOR . 'smoke-plugin.php',
        "<?php\n/**\n * Plugin Name: Smoke Plugin\n * Text Domain: smoke-plugin\n */\n"
    );

    run(
        [
            PHP_BINARY,
            $project . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wp24h-make-module',
            '--class=HardeningModule',
            '--id=hardening',
            '--label=Audit */ log',
            '--description=Checks generator hardening.',
        ],
        $project
    );

    $modulePath = $project . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'HardeningModule.php';
    $module = readRequired($modulePath);

    assertContains('Audit * / log module.', $module, 'neutralized DocBlock label');
    assertContains("'Audit */ log'", $module, 'original user-facing label');

    run([PHP_BINARY, '-l', $modulePath], $project);
}

function testScaffolder(string $root, string $temp): void
{
    $scaffolder = $root . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wp24h-init';

    $badNameTarget = $temp . DIRECTORY_SEPARATOR . 'bad-name';
    runExpectFailure(
        [
            PHP_BINARY,
            $scaffolder,
            "--name=Bad\nName",
            '--slug=bad-name',
            '--namespace=Smoke\\BadName',
            '--vendor=smoke',
            '--target=' . $badNameTarget,
        ],
        $root
    );
    assertMissing($badNameTarget, 'invalid-name target');

    $badAuthorTarget = $temp . DIRECTORY_SEPARATOR . 'bad-author';
    runExpectFailure(
        [
            PHP_BINARY,
            $scaffolder,
            '--name=Bad Author Plugin',
            '--slug=bad-author',
            '--namespace=Smoke\\BadAuthor',
            '--vendor=smoke',
            '--author=Bad */ Author',
            '--target=' . $badAuthorTarget,
        ],
        $root
    );
    assertMissing($badAuthorTarget, 'invalid-author target');

    $validTarget = $temp . DIRECTORY_SEPARATOR . 'literal-uri';
    run(
        [
            PHP_BINARY,
            $scaffolder,
            '--name=Café Tools',
            '--slug=cafe-tools',
            '--namespace=Smoke\\CafeTools',
            '--vendor=smoke',
            '--author=Acme & Co.',
            '--plugin-uri=https://example.com/$1/repository',
            '--target=' . $validTarget,
        ],
        $root
    );

    $main = readRequired($validTarget . DIRECTORY_SEPARATOR . 'cafe-tools.php');
    assertContains('Plugin Name: Café Tools', $main, 'Unicode plugin name');
    assertContains('Author:      Acme & Co.', $main, 'ordinary author punctuation');
    assertContains('Plugin URI:  https://example.com/$1/repository', $main, 'literal plugin URI');

    run([PHP_BINARY, '-l', $validTarget . DIRECTORY_SEPARATOR . 'cafe-tools.php'], $validTarget);
}

/**
 * @param list<string> $command
 */
function run(array $command, string $cwd): void
{
    [$exitCode, $stdout, $stderr] = execute($command, $cwd);

    if ($exitCode !== 0) {
        throw new RuntimeException(
            sprintf(
                "Command failed (%d): %s\n%s\n%s",
                $exitCode,
                implode(' ', $command),
                trim($stdout),
                trim($stderr)
            )
        );
    }
}

/**
 * @param list<string> $command
 */
function runExpectFailure(array $command, string $cwd): void
{
    [$exitCode] = execute($command, $cwd);

    if ($exitCode === 0) {
        throw new RuntimeException('Command was expected to fail but succeeded: ' . implode(' ', $command));
    }
}

/**
 * @param list<string> $command
 * @return array{0:int,1:string,2:string}
 */
function execute(array $command, string $cwd): array
{
    $process = proc_open(
        $command,
        [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
        $cwd
    );

    if (!is_resource($process)) {
        throw new RuntimeException('Could not start process: ' . implode(' ', $command));
    }

    fclose($pipes[0]);
    $stdout = (string) stream_get_contents($pipes[1]);
    $stderr = (string) stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    return [proc_close($process), $stdout, $stderr];
}

function readRequired(string $path): string
{
    $contents = file_get_contents($path);
    if ($contents === false) {
        throw new RuntimeException('Expected file could not be read: ' . $path);
    }

    return $contents;
}

function assertContains(string $needle, string $haystack, string $label): void
{
    if (!str_contains($haystack, $needle)) {
        throw new RuntimeException(sprintf('%s is missing expected value: %s', $label, $needle));
    }
}

function assertMissing(string $path, string $label): void
{
    if (file_exists($path)) {
        throw new RuntimeException($label . ' should not exist after rejected input.');
    }
}

function removeTree(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }

    foreach (new DirectoryIterator($path) as $item) {
        if ($item->isDot()) {
            continue;
        }
        removeTree($item->getPathname());
    }

    @rmdir($path);
}
