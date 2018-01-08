
#!/bin/bash
# This file runs during the frontend setup.

set -e

docrootDir="$1"

echo -en "travis_fold:start:FE-Setup"
printf "Start - Installing npm for transac themes"
cd $docrootDir/themes/custom/transac
npm run install-tools
printf "End - Installing npm for transac themes"

printf "Start - Installing npm for non-transac themes"
cd $docrootDir/themes/custom/non_transac
npm run install-tools
printf "Start - Installing npm for non-transac themes"
echo -en "travis_fold:end:FE-Setup"
