#!/bin/bash

stack="$1"
site="$2"

script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

AH_SITE_NAME=`drush @$stack ssh 'echo $AH_SITE_NAME'`
echo $AH_SITE_NAME;

echo $site
command="cd /var/www/html/$AH_SITE_NAME/docroot; drush -l https://$site crf; curl -I 'http://127.0.0.1:9091/en/search/?test=1' -H 'Host:$site' -H 'X-Forwarded-Proto: https'"
echo $command
drush @$stack ssh "$command"
sleep 60
php "$script_dir/clear_cf_html_cache.php" $site
echo ""
