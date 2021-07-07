#!/bin/bash

# This script executes a drush command and clears CF cache only for production sites.

command="$1"
sleep="$2"

script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
docroot="/var/www/html/$AH_SITE_NAME/docroot"

for site in `drush --root=$docroot sfl | grep "1: " | grep ".com" | tr "1: " " " | tr -d " "`
do
  $script_dir/clear_caches_post_command_site.sh $site $command $sleep
done;
