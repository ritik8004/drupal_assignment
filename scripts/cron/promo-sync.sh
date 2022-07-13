#!/bin/bash
#
# Script to run cart promotion sync and attach/detach queue via cron.
#

cd /var/www/html/${AH_SITE_NAME}/docroot

# Stack 1 contains all non-transac and transac lite sites.
# We only need to run promo-sync for BBW in Stack 1.
# AH_SITE_GROUP env variable value is 'alshaya' for Stack 1.
filter=''
if [[ ${AH_SITE_GROUP} == 'alshaya' ]]; then
  filter=" --sites-filter='name*=bbw'"
fi

cartPromoSyncTime=`date`
echo "Running cart promotion sync on" ${cartPromoSyncTime} &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-promo-sync.log
drush acsf-tools-ml ${filter} acspm "'--types=cart'" --verbose &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-promo-sync.log

catalogPromSyncTime=`date`
echo "Running catalog promotion sync on" ${catalogPromSyncTime} &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-promo-sync.log
drush acsf-tools-ml ${filter} acspm "'--types=category'" --verbose &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-promo-sync.log

attachQueueTime=`date`
echo "Running attach queue on" ${attachQueueTime} &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-promo-sync.log
drush acsf-tools-ml ${filter} queue-run acq_promotion_attach_queue --verbose &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-promo-sync.log

promoProcessEndTime=`date`
echo "Promo sync and queue processing is completed on" ${promoProcessEndTime} &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-promo-sync.log
