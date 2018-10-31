uri="$1"

# Check status once so hook_drush8_command_alter is triggered.
drush --uri=$uri status

# Clear cache, we want to avoid fatal errors because of updated services.
drush --uri=$uri cr

# Enable developer modules, we are going to use this script only on non-prod envs.
echo "Enabling developer modules."
drush  --uri=$uri pm:enable -y dblog views_ui features_ui restui

# Disable shield for product push to work.
echo "Disabling shield."
drush --uri=$uri pm-uninstall -y shield

echo "Disabling logs_http."
drush --uri=$uri pm-uninstall -y logs_http

echo "Disabling all search api indexes."
drush --uri=$uri search-api-disable-all