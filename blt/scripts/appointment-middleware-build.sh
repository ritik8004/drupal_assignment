#!/usr/bin/env bash
# This file runs during the appointment middleware build.

appointmentMiddlewareDir="$1"

echo "Building appointment middleware."

cd $appointmentMiddlewareDir
composer validate --no-check-all --ansi
composer install --no-interaction
