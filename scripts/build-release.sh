#!/usr/bin/env bash
set -euo pipefail

SLUG="wp24h-plugin-boilerplate"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
OUTPUT_DIR="${1:-$ROOT_DIR/dist}"
PACKAGE_DIR="${RUNNER_TEMP:-${TMPDIR:-/tmp}}/$SLUG"
ZIP_PATH="$OUTPUT_DIR/$SLUG.zip"

rm -rf "$PACKAGE_DIR"
mkdir -p "$PACKAGE_DIR" "$OUTPUT_DIR"

rsync -a --delete --exclude-from="$ROOT_DIR/.distignore" "$ROOT_DIR/" "$PACKAGE_DIR/"

rm -f "$ZIP_PATH"
(
  cd "$(dirname "$PACKAGE_DIR")"
  zip -qr "$ZIP_PATH" "$SLUG"
)

printf 'Built %s\n' "$ZIP_PATH"
