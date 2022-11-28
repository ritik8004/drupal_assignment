#!/usr/bin/bash -r
# This script prepare behat profiles on container.

# Check arguments
if [ -z $1 ] || [ -z $2 ]; then
  echo "[ERROR] Missing arguments"
  echo "Usage: $0 [container name] [Brand-Market-Env-Language]";
  echo "  example: jenkins_appserver $0 vs-ae-uat-en";
  exit 1
fi
CONTAINER=$1
SITE=$2

docker exec -t ${CONTAINER} sh -c "cd /behat && ./behat-build.sh --rebuild=TRUE --site=${SITE}"
