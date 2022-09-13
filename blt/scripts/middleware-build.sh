#!/usr/bin/env bash
# This file runs during the middleware build.

set -ev

deployDir="$1"

echo "Building Proxy middleware."

cd "$deployDir/docroot/proxy"
composer validate --no-check-all --ansi
composer install --no-interaction
cd "-"

set +v
