#!/usr/bin/env sh

set -eu

npm ci --ignore-scripts --no-audit

exec npm run dev -- --host 0.0.0.0 --port 5173
