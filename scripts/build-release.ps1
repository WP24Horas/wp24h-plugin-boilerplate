param(
    [string]$OutputDirectory = (Join-Path (Split-Path -Parent $PSScriptRoot) 'dist')
)

$ErrorActionPreference = 'Stop'

$slug = 'wp24h-plugin-boilerplate'
$root = (Resolve-Path (Split-Path -Parent $PSScriptRoot)).Path.TrimEnd('\', '/')
$tempRoot = Join-Path ([System.IO.Path]::GetTempPath()) ($slug + '-' + [Guid]::NewGuid().ToString('N'))
$packageDir = Join-Path $tempRoot $slug
$zipFile = Join-Path $OutputDirectory ($slug + '.zip')

function Test-IgnoredPath {
    param([string]$RelativePath)

    $normalized = $RelativePath.Replace('\', '/')
    $ignored = @(
        '.git', '.github', '.wp-env', '.wp-env.json', '.phpunit.cache',
        'bin', 'docs', 'tests', 'vendor', 'scripts', 'dist',
        '.editorconfig', '.gitignore', '.distignore',
        'composer.json', 'composer.lock',
        'phpcs.xml.dist', 'phpstan.neon.dist', 'phpunit.xml.dist',
        'CONTRIBUTING.md', 'SECURITY.md', 'README.md', 'CHANGELOG.md', 'CODE_OF_CONDUCT.md'
    )

    foreach ($item in $ignored) {
        if ($normalized -eq $item -or $normalized.StartsWith($item + '/')) {
            return $true
        }
    }

    return $normalized.EndsWith('.zip', [System.StringComparison]::OrdinalIgnoreCase)
}

try {
    New-Item -ItemType Directory -Force -Path $packageDir, $OutputDirectory | Out-Null

    Get-ChildItem -LiteralPath $root -Recurse -Force | ForEach-Object {
        $relative = $_.FullName.Substring($root.Length).TrimStart('\', '/')
        if (Test-IgnoredPath $relative) {
            return
        }

        $destination = Join-Path $packageDir $relative
        if ($_.PSIsContainer) {
            New-Item -ItemType Directory -Force -Path $destination | Out-Null
        } else {
            $parent = Split-Path -Parent $destination
            New-Item -ItemType Directory -Force -Path $parent | Out-Null
            Copy-Item -LiteralPath $_.FullName -Destination $destination -Force
        }
    }

    foreach ($required in @("$slug.php", 'readme.txt', 'LICENSE.md', 'src')) {
        if (-not (Test-Path -LiteralPath (Join-Path $packageDir $required))) {
            throw "Required release path missing: $required"
        }
    }

    if (Test-Path -LiteralPath $zipFile) {
        Remove-Item -LiteralPath $zipFile -Force
    }

    Compress-Archive -Path $packageDir -DestinationPath $zipFile -CompressionLevel Optimal
    Write-Host "Built $zipFile"
} finally {
    if (Test-Path -LiteralPath $tempRoot) {
        Remove-Item -LiteralPath $tempRoot -Recurse -Force
    }
}
