#!/bin/bash
#
# Script to run cart promotion sync and attach/detach queue via cron.
#

cd /var/www/html/${AH_SITE_NAME}/docroot

promoSyncTime=`date`
echo "Running cart promotion sync on" ${promoSyncTime}
drush acsf-tools-ml --profiles=alshaya_transac acspm --types='cart' --verbose

attachQueueTime=`date`
echo "Running attach queue on" ${attachQueueTime}
drush acsf-tools-ml queue-run acq_promotion_attach_queue --verbose

detachQueueTime=`date`
echo "Running detach queue on" ${detachQueueTime}
drush acsf-tools-ml queue-run acq_promotion_detach_queue --verbose
