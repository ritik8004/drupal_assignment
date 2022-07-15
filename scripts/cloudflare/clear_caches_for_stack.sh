#!/bin/bash

script_dir="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

cd /var/www/html/$AH_SITE_NAME/docroot;

sites=`drush sfl | grep "1: " | grep -E "(www|hm.com|boots.com|cosstores.com)" | tr "1: " " "`
echo $sites;

for site in $sites
do
  echo $site

  # Clear Drupal cache.
  drush -l https://$site crf

  # Clear Varnish cache.
  drush -l https://$site p:invalidate everything -y

  # Clear Cloudflare cache for HTML pages.
  php "$script_dir/clear_cf_html_cache.php" $site
  echo ""
done;
