#!/bin/bash
#
# ./backup_important_files.sh
#
# Backup and archive the important files.
#

TIME=`date +%b-%d-%y`
BACKUP_FILE=backup-$TIME.tar.gz
DEST=$HOME/backup_important_files/$BACKUP_FILE

PPROD_SSH=alshaya.01pprod@staging-1510.enterprise-g1.hosting.acquia.com

# Create backup directory on home.
mkdir -p $HOME/backup_important_files

# Files/Directories for which backup needs to be done.
FILE1=$HOME/rabbitmq-creds
FILE2=$HOME/settings
FILE3=$HOME/knet-resource
FILE4=$HOME/safe

# Archive the files.
tar czf $DEST $FILE1 $FILE2 $FILE3 $FILE4
echo "Backup $BACKUP_FILE is saved in $DEST"

# Copying the backup archive on pprod env.
scp $DEST $PPROD_SSH:~/
echo "Backup is copied to the pprod env."
