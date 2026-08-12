<?php

declare(strict_types=1);

$full = in_array('--full', $argv, true);
$root = dirname(__DIR__);
$tempBase = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'wp24h-scaffold-smoke-' . bin2hex(random_bytes(4));
$target = $tempBase . DIRECTORY_SEPARATOR . 'acme-orders';

$command = [
    PHP_BINARY,
    $root . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wp24h-init',
    '--name=Acme Orders',
    '--slug=acme-orders',
    '--namespace=Acme\\Orders',
    '--vendor=acme',
    '--author=Acme Inc.',
    '--author-uri=https://example.com',
    '--target=' . $target,
];

try {
    run($command, $root);

    assertFile($target . DIRECTORY_SEPARATOR . 'acme-orders.php');
    assertMissing($target . DIRECTORY_SEPARATOR . 'wp24h-plugin-boilerplate.php');
    assertMissing($target . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wp24h-init');
    assertMissing($target . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'smoke-scaffold.php');
    assertMissing($target . DIRECTORY_SEPARATOR . 'composer.lock');

    $composerPath = $target . DIRECTORY_SEPARATOR . 'composer.json';
    assertFile($composerPath);

    $composer = json_decode((string) file_get_contents($composerPath), true, 512, JSON_THROW_ON_ERROR);
    assertSame('acme/acme-orders', $composer['name'] ?? null, 'Composer package name');
    assertSame('src/', $composer['autoload']['psr-4']['Acme\\Orders\\'] ?? null, 'Composer PSR-4 namespace');
    assertFalse(isset($composer['scripts']['scaffold']), 'Generated plugin must not keep the scaffold command.');
    assertFalse(isset($composer['scripts']['scaffold:smoke']), 'Generated plugin must not keep scaffold smoke commands.');
    assertFalse(isset($composer['scripts']['scaffold:smoke:full']), 'Generated plugin must not keep full scaffold smoke commands.');

    $main = (string) file_get_contents($target . DIRECTORY_SEPARATOR . 'acme-orders.php');
    assertContains('Plugin Name: Acme Orders', $main, 'plugin name');
    assertContains('Text Domain: acme-orders', $main, 'text domain');
    assertContains("define( 'ACME_ORDERS_VERSION'", $main, 'constant prefix');
    assertContains('Acme\\Orders\\Core\\Plugin::class', $main, 'runtime namespace');

    $options = (string) file_get_contents($target . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Support' . DIRECTORY_SEPARATOR . 'Options.php');
    assertContains("public const KEY = 'acme_orders_settings';", $options, 'option key');
    assertContains("'rest_namespace' => 'acme-orders/v1'", $options, 'REST namespace');

    $plugin = (string) file_get_contents($target . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Plugin.php');
    assertContains("do_action( 'acme_orders_loaded'", $plugin, 'public action name');
    assertContains("apply_filters( 'acme_orders_modules'", $plugin, 'public module filter');

    assertNoLegacyIdentity(
        $target,
        [
            'wp24h-plugin-boilerplate',
            'wp24h_plugin_boilerplate',
            'WP24H_PLUGIN_BOILERPLATE',
            'WP24H\\PluginBoilerplate',
            'wp24h-boilerplate',
        ],
        [
            'README.md',
            'CHANGELOG.md',
            'docs/',
            'LICENSE.md',
        ]
    );

    if ($full) {
        $composerBinary = findComposer();
        run([$composerBinary, 'install', '--no-interaction', '--prefer-dist'], $target);
        run([$composerBinary, 'check'], $target);
    }

    fwrite(STDOUT, "Scaffold smoke test passed.\n");
    if (!$full) {
        fwrite(STDOUT, "Run with --full to also execute composer install + composer check in the generated plugin.\n");
    }
} finally {
    removeTree($tempBase);
}

/**
 * @param list<string> $command
 */
function run(array $command, string $cwd): void
{
    $escaped = array_map('escapeshellarg', $command);
    $process = proc_open(
        implode(' ', $escaped),
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
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);
    if ($exitCode !== 0) {
        throw new RuntimeException(
            sprintf(
                "Command failed (%d): %s\n%s\n%s",
                $exitCode,
                implode(' ', $command),
                trim((string) $stdout),
                trim((string) $stderr)
            )
        );
    }
}

function assertFile(string $path): void
{
    if (!is_file($path)) {
        throw new RuntimeException('Expected file does not exist: ' . $path);
    }
}

function assertMissing(string $path): void
{
    if (file_exists($path)) {
        throw new RuntimeException('Path should not exist in generated plugin: ' . $path);
    }
}

function assertSame(mixed $expected, mixed $actual, string $label): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf('%s mismatch. Expected %s, got %s.', $label, var_export($expected, true), var_export($actual, true)));
    }
}

function assertFalse(bool $condition, string $message): void
{
    if ($condition) {
        throw new RuntimeException($message);
    }
}

function assertContains(string $needle, string $haystack, string $label): void
{
    if (!str_contains($haystack, $needle)) {
        throw new RuntimeException(sprintf('Generated %s is missing expected value: %s', $label, $needle));
    }
}

/**
 * @param list<string> $tokens
 * @param list<string> $ignoredPrefixes
 */
function assertNoLegacyIdentity(string $target, array $tokens, array $ignoredPrefixes): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($target) + 1));
        foreach ($ignoredPrefixes as $ignored) {
            if ($relative === $ignored || str_starts_with($relative, $ignored)) {
                continue 2;
            }
        }

        $contents = file_get_contents($file->getPathname());
        if ($contents === false || str_contains($contents, "\0")) {
            continue;
        }

        foreach ($tokens as $token) {
            if (str_contains($contents, $token)) {
                throw new RuntimeException(sprintf('Legacy identity "%s" leaked into %s.', $token, $relative));
            }
        }
    }
}

function findComposer(): string
{
    $candidates = DIRECTORY_SEPARATOR === '\\' ? ['composer.bat', 'composer'] : ['composer'];

    foreach ($candidates as $candidate) {
        $output = [];
        $exitCode = 1;
        $check = DIRECTORY_SEPARATOR === '\\'
            ? 'where ' . escapeshellarg($candidate)
            : 'command -v ' . escapeshellarg($candidate);
        exec($check, $output, $exitCode);
        if ($exitCode === 0) {
            return $candidate;
        }
    }

    throw new RuntimeException('Composer was not found in PATH for full scaffold smoke test.');
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
