#!/usr/bin/env bash
set -euo pipefail

SLUG="wp24h-plugin-boilerplate"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ZIP_PATH="${1:-$ROOT_DIR/dist/$SLUG.zip}"

if ! command -v unzip >/dev/null 2>&1; then
  printf 'Required command not found: unzip\n' >&2
  exit 1
fi

if [[ ! -f "$ZIP_PATH" ]]; then
  printf 'Release ZIP not found: %s\n' "$ZIP_PATH" >&2
  exit 1
fi

mapfile -t entries < <(unzip -Z1 "$ZIP_PATH")

if [[ ${#entries[@]} -eq 0 ]]; then
  printf 'Release ZIP is empty: %s\n' "$ZIP_PATH" >&2
  exit 1
fi

prefix="$SLUG/"

for entry in "${entries[@]}"; do
  if [[ "$entry" != "$prefix"* ]]; then
    printf 'Unexpected top-level entry: %s\n' "$entry" >&2
    exit 1
  fi
done

required=(
  "$SLUG/$SLUG.php"
  "$SLUG/readme.txt"
  "$SLUG/LICENSE.md"
)

for path in "${required[@]}"; do
  if ! printf '%s\n' "${entries[@]}" | grep -Fxq "$path"; then
    printf 'Required release file missing: %s\n' "$path" >&2
    exit 1
  fi
done

forbidden_prefixes=(
  "$SLUG/.git/"
  "$SLUG/.github/"
  "$SLUG/bin/"
  "$SLUG/docs/"
  "$SLUG/tests/"
  "$SLUG/vendor/"
  "$SLUG/scripts/"
  "$SLUG/dist/"
)

for prefix_path in "${forbidden_prefixes[@]}"; do
  if printf '%s\n' "${entries[@]}" | grep -Fq "$prefix_path"; then
    printf 'Forbidden release path found: %s\n' "$prefix_path" >&2
    exit 1
  fi
done

forbidden_files=(
  "$SLUG/composer.json"
  "$SLUG/composer.lock"
  "$SLUG/phpcs.xml.dist"
  "$SLUG/phpstan.neon.dist"
  "$SLUG/phpunit.xml.dist"
  "$SLUG/CONTRIBUTING.md"
  "$SLUG/SECURITY.md"
  "$SLUG/.editorconfig"
  "$SLUG/.gitignore"
  "$SLUG/.wp-env.json"
)

for path in "${forbidden_files[@]}"; do
  if printf '%s\n' "${entries[@]}" | grep -Fxq "$path"; then
    printf 'Forbidden release file found: %s\n' "$path" >&2
    exit 1
  fi
done

for entry in "${entries[@]}"; do
  case "$entry" in
    *.zip)
      printf 'Nested ZIP must not be included: %s\n' "$entry" >&2
      exit 1
      ;;
  esac
done

printf 'Release ZIP verified: %s (%d entries)\n' "$ZIP_PATH" "${#entries[@]}"
