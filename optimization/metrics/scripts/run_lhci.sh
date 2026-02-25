#!/usr/bin/env bash
set -euo pipefail

# Runs Lighthouse CI locally against a URL or a local ./site/ build.
# Requires: npm i -g @lhci/cli
# Usage: ./scripts/run_lhci.sh https://yoursite.example

URL="${1:-https://example.com}"
echo "Running Lighthouse CI for: $URL"
lhci collect --url="$URL"
lhci assert
echo "Done."