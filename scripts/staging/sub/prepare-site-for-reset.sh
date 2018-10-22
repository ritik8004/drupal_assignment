uri="$1"

# Check status once so hook_drush8_command_alter is triggered.
drush8 --uri=$uri status

# Clear cache, we want to avoid fatal errors because of updated services.
drush8 --uri=$uri cr

# Enable developer modules, we are going to use this script only on non-prod envs.
echo "Enabling developer modules."
drush8  --uri=$uri en -y dblog views_ui features_ui restui

# Disable shield for product push to work.
echo "Disabling shield."
drush8 --uri=$uri pm-uninstall -y shield

echo "Disabling all search api indexes."
drush8 --uri=$uri search-api-disable-all