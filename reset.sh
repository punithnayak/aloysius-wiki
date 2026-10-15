#!/usr/bin/env bash
# Deletes your wiki (pages, edits, accounts) so you can start fresh with bash start.sh
# Your edits to workshop/ and extensions/ are kept.
set -euo pipefail
cd "$(dirname "$0")"
docker rm -f aloysius-wiki >/dev/null 2>&1 || true
docker run --rm -v "$PWD/data:/d" mediawiki:1.43 sh -c 'rm -rf /d/*' 2>/dev/null || sudo rm -rf data/*
echo "🧹 Wiki deleted. Run bash start.sh to make a fresh one."
