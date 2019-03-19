#!/bin/bash
#
# Script to run cart promotion sync and attach/detach queue via cron.
#

cd /var/www/html/${AH_SITE_NAME}/docroot

cartPromoSyncTime=`date`
echo "Running cart promotion sync on" ${cartPromoSyncTime}
drush acsf-tools-ml --profiles=alshaya_transac acspm --types='cart' --verbose &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-promo-sync.log

catalogPromSyncTime=`date`
echo "Running catalog promotion sync on" ${catalogPromSyncTime}
drush acsf-tools-ml --profiles=alshaya_transac acspm --types='category' --verbose &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-promo-sync.log

attachQueueTime=`date`
echo "Running attach queue on" ${attachQueueTime}
drush acsf-tools-ml queue-run acq_promotion_attach_queue --verbose &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-promo-sync.log

detachQueueTime=`date`
echo "Running detach queue on" ${detachQueueTime}
drush acsf-tools-ml queue-run acq_promotion_detach_queue --verbose &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-promo-sync.log

promoProcessEndTime=`date`
echo "Promo sync and queue processing is completed on" ${promoProcessEndTime}
