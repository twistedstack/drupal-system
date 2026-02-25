#!/usr/bin/env bash
set -euo pipefail

# Simple GTmetrix API example (v2). Replace placeholders.
API_USER="you@example.com"
API_KEY="GTMETRIX_API_KEY"
TEST_URL="${1:-https://example.com}"
LOCATION="${2:-1}" # 1=Vancouver, see GTmetrix docs

echo "Triggering GTmetrix test for $TEST_URL"
RESP=$(curl -s -u "$API_USER:$API_KEY" -H "Content-Type: application/json" -d "{"url":"$TEST_URL","location":"$LOCATION"}" https://gtmetrix.com/api/2.0/tests)
echo "$RESP"