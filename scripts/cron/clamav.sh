#!/bin/bash
#
# Script to run antivirus scan.
#

startTime=`date`
echo "Running ClamAV against www repository at" ${startTime} &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-clamav.log
summary=$(/usr/bin/clamscan -ri /var/www/html/${AH_SITE_NAME} --follow-dir-symlinks=2 --exclude='\.(jpg|jpeg|png|gif)$' --exclude='/var/www/html/alshaya01.*/docroot/sites/g/files')
echo $summary &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-clamav.log
echo $summary | mail -s "Alshaya: ClamAV results for www repository" sylvain.delbosc@acquia.com,prafful.nagwani@acquia.com

startTime=`date`
echo "Running ClamAV against home repository at" ${startTime} &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-clamav.log
summary=$(/usr/bin/clamscan -ri $HOME --follow-dir-symlinks=0 --exclude='\.(jpg|jpeg|png|gif)$')
echo $summary &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-clamav.log
echo $summary | mail -s "Alshaya: ClamAV results for home repository" sylvain.delbosc@acquia.com,prafful.nagwani@acquia.com

startTime=`date`
echo "Running ClamAV against tmp repository at" ${startTime} &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-clamav.log
summary=$(/usr/bin/clamscan -ri /tmp --follow-dir-symlinks=0 --exclude='\.(jpg|jpeg|png|gif)$')
echo $summary &>> /var/log/sites/${AH_SITE_NAME}/logs/$(hostname -s)/alshaya-clamav.log
echo $summary | mail -s "Alshaya: ClamAV results for tmp repository" sylvain.delbosc@acquia.com,prafful.nagwani@acquia.com
