#!/usr/bin/env bash
# This file runs during the middleware build.

middlewareDir="$1"

cd $middlewareDir
composer install --no-interaction
