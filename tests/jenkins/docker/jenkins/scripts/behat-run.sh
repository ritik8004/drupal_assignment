#!/usr/bin/bash -r
# This script runs behat tests on container.

# Check arguments
if [ -z $1 ] || [ -z $2 ]; then
  echo "[ERROR] Missing arguments"
  echo "Usage: $0 [container name] [Brand-Market-Env-Language-Device] [tags]";
  echo "  example: jenkins_appserver $0 vs-ae-uat-en-desktop @smoke";
  exit 1
fi
CONTAINER=$1
PROFILE=$2
TAGS=$3

docker exec -t ${CONTAINER} sh -c "cd /behat && ./bin/behat --profile=${PROFILE} --format cucumber_json --tags=${TAGS}"
