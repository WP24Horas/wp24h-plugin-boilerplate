<?php

declare(strict_types=1);

$full = in_array('--full', $argv, true);
$root = dirname(__DIR__);
$tempBase = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'wp24h-scaffold-smoke-' . bin2hex(random_bytes(4));
$target = $tempBase . DIRECTORY_SEPARATOR . 'acme-orders';
$targetWithUri = $tempBase . DIRECTORY_SEPARATOR . 'acme-orders-with-uri';

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
    assertFile($target . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wp24h-make-module');
    assertMissing($target . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'smoke-scaffold.php');
    assertFile($target . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'lint-tooling.php');
    assertFile($target . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'check-generator-hardening.php');
    assertMissing($target . DIRECTORY_SEPARATOR . 'composer.lock');

    $composerPath = $target . DIRECTORY_SEPARATOR . 'composer.json';
    assertFile($composerPath);

    $composer = json_decode((string) file_get_contents($composerPath), true, 512, JSON_THROW_ON_ERROR);
    assertSame('acme/acme-orders', $composer['name'] ?? null, 'Composer package name');
    assertSame('src/', $composer['autoload']['psr-4']['Acme\\Orders\\'] ?? null, 'Composer PSR-4 namespace');
    assertSame('php bin/wp24h-make-module', $composer['scripts']['make:module'] ?? null, 'module generator command');
    assertSame('php scripts/lint-tooling.php', $composer['scripts']['tooling:lint'] ?? null, 'tooling lint command');
    assertSame('php scripts/check-generator-hardening.php', $composer['scripts']['tooling:hardening'] ?? null, 'tooling hardening command');
    assertFalse(isset($composer['scripts']['scaffold']), 'Generated plugin must not keep the scaffold command.');
    assertFalse(isset($composer['scripts']['scaffold:smoke']), 'Generated plugin must not keep scaffold smoke commands.');
    assertFalse(isset($composer['scripts']['scaffold:smoke:full']), 'Generated plugin must not keep full scaffold smoke commands.');
    assertSame(['@lint', '@analyse', '@test', '@tooling:lint', '@tooling:hardening'], $composer['scripts']['check'] ?? null, 'generated plugin check contract');

    $main = (string) file_get_contents($target . DIRECTORY_SEPARATOR . 'acme-orders.php');
    assertContains('Plugin Name: Acme Orders', $main, 'plugin name');
    assertContains('Text Domain: acme-orders', $main, 'text domain');
    assertContains("define( 'ACME_ORDERS_VERSION'", $main, 'constant prefix');
    assertContains('Acme\\Orders\\Core\\Plugin::class', $main, 'runtime namespace');
    assertNotContains('Plugin URI:', $main, 'plugin header without explicit URI');
    assertNotContains('github.com/WP24Horas/acme-orders', $main, 'invented repository URL');

    $security = (string) file_get_contents($target . DIRECTORY_SEPARATOR . 'SECURITY.md');
    assertNotContains('contact WP24Horas', $security, 'generated security policy ownership');
    assertContains('https://example.com', $security, 'maintainer contact hint');

    $wpOrgReadme = (string) file_get_contents($target . DIRECTORY_SEPARATOR . 'readme.txt');
    assertContains('=== Acme Orders ===', $wpOrgReadme, 'WordPress.org readme title');
    assertContains('Stable tag: 1.0.0', $wpOrgReadme, 'WordPress.org stable tag');
    assertNotContains('Contributors: asllanmaciel', $wpOrgReadme, 'inherited WordPress.org contributor');
    assertNotContains('This repository is intended as a development starter', $wpOrgReadme, 'inherited boilerplate instructions');

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

    run(
        [
            PHP_BINARY,
            $target . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wp24h-make-module',
            '--class=AuditLogModule',
            '--id=audit_log',
            '--label=Audit log',
            '--description=Registers audit log hooks.',
        ],
        $target
    );

    $generatedModulePath = $target . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR . 'AuditLogModule.php';
    $generatedModuleTestPath = $target . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'Unit' . DIRECTORY_SEPARATOR . 'AuditLogModuleTest.php';
    assertFile($generatedModulePath);
    assertFile($generatedModuleTestPath);

    $generatedModule = (string) file_get_contents($generatedModulePath);
    assertContains('namespace Acme\\Orders\\Modules;', $generatedModule, 'generated module namespace');
    assertContains("return 'audit_log';", $generatedModule, 'generated module id');
    assertContains("'acme-orders'", $generatedModule, 'generated module text domain');

    $generatedModuleTest = (string) file_get_contents($generatedModuleTestPath);
    assertContains('namespace Acme\\Orders\\Tests\\Unit;', $generatedModuleTest, 'generated module test namespace');
    assertContains('use Acme\\Orders\\Modules\\AuditLogModule;', $generatedModuleTest, 'generated module test import');

    runExpectFailure(
        [
            PHP_BINARY,
            $target . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wp24h-make-module',
            '--class=AuditLogModule',
            '--id=audit_log',
            '--label=Audit log',
        ],
        $target
    );

    run(
        [
            PHP_BINARY,
            $root . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'wp24h-init',
            '--name=Acme Orders',
            '--slug=acme-orders-uri',
            '--namespace=Acme\\OrdersUri',
            '--vendor=acme',
            '--plugin-uri=https://github.com/acme/acme-orders',
            '--target=' . $targetWithUri,
        ],
        $root
    );

    $mainWithUri = (string) file_get_contents($targetWithUri . DIRECTORY_SEPARATOR . 'acme-orders-uri.php');
    assertContains('Plugin URI:  https://github.com/acme/acme-orders', $mainWithUri, 'explicit plugin URI');
    assertNotContains('github.com/WP24Horas/acme-orders-uri', $mainWithUri, 'invented explicit repository URL');

    if ($full) {
        $composerBinary = findComposer();
        run([$composerBinary, 'install', '--no-interaction', '--prefer-dist'], $target);
        run([$composerBinary, 'check'], $target);
    }

    fwrite(STDOUT, "Scaffold and module-generator smoke test passed.\n");
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
    $stdout = (string) stream_get_contents($pipes[1]);
    $stderr = (string) stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    return [proc_close($process), $stdout, $stderr];
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

function assertNotContains(string $needle, string $haystack, string $label): void
{
    if (str_contains($haystack, $needle)) {
        throw new RuntimeException(sprintf('Generated %s contains unexpected value: %s', $label, $needle));
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
