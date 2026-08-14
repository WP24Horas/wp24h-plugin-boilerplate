#!/usr/bin/env bash
set -euo pipefail

SLUG="wp24h-plugin-boilerplate"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUTPUT_DIR="${1:-$ROOT_DIR/dist}"
TEMP_BASE="${RUNNER_TEMP:-${TMPDIR:-/tmp}}"
TEMP_ROOT="$(mktemp -d "$TEMP_BASE/${SLUG}.XXXXXX")"
PACKAGE_DIR="$TEMP_ROOT/$SLUG"
ZIP_PATH="$OUTPUT_DIR/$SLUG.zip"

cleanup() {
  rm -rf "$TEMP_ROOT"
}
trap cleanup EXIT

for command_name in rsync zip; do
  if ! command -v "$command_name" >/dev/null 2>&1; then
    printf 'Required command not found: %s\n' "$command_name" >&2
    exit 1
  fi
done

mkdir -p "$PACKAGE_DIR" "$OUTPUT_DIR"

rsync -a --delete --exclude-from="$ROOT_DIR/.distignore" "$ROOT_DIR/" "$PACKAGE_DIR/"

rm -f "$ZIP_PATH"
(
  cd "$TEMP_ROOT"
  zip -qr "$ZIP_PATH" "$SLUG"
)

printf 'Built %s\n' "$ZIP_PATH"
