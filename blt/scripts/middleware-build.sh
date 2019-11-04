#!/usr/bin/env bash
# This file runs during the middleware build.

middlewareDir="$1"

cd $middlewareDir
composer validate --no-check-all --ansi
composer install --no-interaction
