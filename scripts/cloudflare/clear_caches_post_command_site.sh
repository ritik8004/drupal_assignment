#!/bin/bash

# This script executes a drush command and clears CF cache only for specific production site.

site="$1"
command="$2"
sleep="$3"

script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
slack_file="${script_dir}/../deployment/post_to_slack.sh"
docroot="/var/www/html/$AH_SITE_NAME/docroot"

echo ""
echo $site

if [ ! "$command" = "" ]
then
  echo "drush --root=$docroot -l https://$site $command"
  drush --root=$docroot -l https://$site $command

  # For all the cases (cr or crf) we do clear Varnish too.
  drush --root=$docroot -l https://$site p-invalidate everything -y

  sleep $sleep

  sh $slack_file "Command $command executed on $site"
fi

php "$script_dir/clear_cf_html_cache.php" $site
sh $slack_file "Cloudflare caches cleared for $site"
echo ""
