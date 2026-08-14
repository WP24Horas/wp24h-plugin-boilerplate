param(
    [string]$ZipPath = (Join-Path (Join-Path (Split-Path -Parent $PSScriptRoot) 'dist') 'wp24h-plugin-boilerplate.zip')
)

$ErrorActionPreference = 'Stop'
$slug = 'wp24h-plugin-boilerplate'

if (-not (Test-Path -LiteralPath $ZipPath -PathType Leaf)) {
    throw "Release ZIP not found: $ZipPath"
}

Add-Type -AssemblyName System.IO.Compression.FileSystem
$archive = [System.IO.Compression.ZipFile]::OpenRead((Resolve-Path $ZipPath).Path)

try {
    $entries = @($archive.Entries | ForEach-Object { $_.FullName.Replace('\', '/') })

    if ($entries.Count -eq 0) {
        throw "Release ZIP is empty: $ZipPath"
    }

    $prefix = "$slug/"
    foreach ($entry in $entries) {
        if (-not $entry.StartsWith($prefix, [System.StringComparison]::Ordinal)) {
            throw "Unexpected top-level entry: $entry"
        }
    }

    foreach ($required in @(
        "$slug/$slug.php",
        "$slug/readme.txt",
        "$slug/LICENSE.md"
    )) {
        if ($entries -notcontains $required) {
            throw "Required release file missing: $required"
        }
    }

    if (-not ($entries | Where-Object { $_.StartsWith("$slug/src/", [System.StringComparison]::Ordinal) })) {
        throw "Required release directory missing: $slug/src/"
    }

    foreach ($forbiddenPrefix in @(
        "$slug/.git/", "$slug/.github/", "$slug/.wp-env/", "$slug/bin/",
        "$slug/docs/", "$slug/tests/", "$slug/vendor/", "$slug/scripts/", "$slug/dist/"
    )) {
        if ($entries | Where-Object { $_.StartsWith($forbiddenPrefix, [System.StringComparison]::Ordinal) }) {
            throw "Forbidden release path found: $forbiddenPrefix"
        }
    }

    foreach ($forbiddenFile in @(
        "$slug/.distignore", "$slug/.editorconfig", "$slug/.gitignore", "$slug/.wp-env.json",
        "$slug/composer.json", "$slug/composer.lock", "$slug/phpcs.xml.dist", "$slug/phpstan.neon.dist", "$slug/phpunit.xml.dist",
        "$slug/CONTRIBUTING.md", "$slug/SECURITY.md", "$slug/README.md", "$slug/CHANGELOG.md", "$slug/CODE_OF_CONDUCT.md"
    )) {
        if ($entries -contains $forbiddenFile) {
            throw "Forbidden release file found: $forbiddenFile"
        }
    }

    foreach ($entry in $entries) {
        if ($entry.EndsWith('.zip', [System.StringComparison]::OrdinalIgnoreCase)) {
            throw "Nested ZIP must not be included: $entry"
        }
    }

    Write-Host "Release ZIP verified: $ZipPath ($($entries.Count) entries)"
} finally {
    $archive.Dispose()
}
