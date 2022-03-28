#!/usr/bin/env bash
# This file runs during the middleware build.

deployDir="$1"

echo "Building Appointment middleware."

cd "$deployDir/docroot/appointment"
composer validate --no-check-all --ansi
composer install --no-interaction

echo "Building Proxy middleware."

cd "$deployDir/docroot/proxy"
composer validate --no-check-all --ansi
composer install --no-interaction
