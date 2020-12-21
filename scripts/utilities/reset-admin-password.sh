#!/bin/bash
#
# ./reset-admin-password.sh "dev"
#

target_env="$1"

for stack in alshaya.01 alshaya2.02 alshaya3bis.01 alshaya4.04 alshaya5.05
do
  alias="@${stack}${target_env}"
  for site in `drush $alias acsf-tools-list --fields`
  do
    url="${site}-${target_env}.factory.alshaya.com"

    # For some sites we have admin1, for some we have siteadmin.
    # Here we do for both, we can safely ignore the errors for other user.
    drush $alias -l $url upwd admin1 AlShAyAU1admin &> /dev/null
    drush $alias -l $url upwd siteadmin AlShAyAU1admin &> /dev/null

    # Mark the reset password status to not required.
    drush $alias -l $url sqlq 'UPDATE user__field_password_expiration SET field_password_expiration_value=0 where entity_id in (select uid from users_field_data where name in ("admin1", "siteadmin"))' &> /dev/null

    # Update the reset time to current time to ensure it is not required again after next cron job.
    drush $alias -l $url sqlq 'UPDATE user__field_last_password_reset SET field_last_password_reset_value=CURRENT_TIMESTAMP() where entity_id in (select uid from users_field_data where name in ("admin1", "siteadmin"))' &> /dev/null

    echo "Password reset for $url"
    echo
  done
done
