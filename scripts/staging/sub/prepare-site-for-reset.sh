site="$1"
target_env="$2"
uri="$3"

if [ $target_env = "01live" -o $target_env = "01update" ]
then
  echo "Lets not try developer scripts on prod env :)"
  exit
fi

# Check status once so hook_drush8_command_alter is triggered.
drush8 @$site.$target_env --uri=$uri status

# Clear cache, we want to avoid fatal errors because of updated services.
drush8 @$site.$target_env --uri=$uri cr

# Enable developer modules, we are going to use this script only on non-prod envs.
echo "Enabling developer modules."
drush8 @$site.$target_env --uri=$uri en -y dblog views_ui features_ui restui

# Disable shield for product push to work.
echo "Disabling shield."
drush8 @$site.$target_env --uri=$uri pm-uninstall -y shield

echo "Disabling all search api indexes."
drush8 @$site.$target_env --uri=$uri search-api-disable-all