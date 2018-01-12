
#!/bin/bash
# This file runs during the frontend setup.

set -e

docrootDir="$1"

echo -en "travis_fold:start:FE-Setup"
echo -en "Start - Installing npm for transac themes"
cd $docrootDir/themes/custom/transac
npm run install-tools
echo -en "End - Installing npm for transac themes"

echo -en "Start - Installing npm for non-transac themes"
cd $docrootDir/themes/custom/non_transac
npm run install-tools
echo -en "End - Installing npm for non-transac themes"

echo -en "Start - Installing npm amp themes"
cd $docrootDir/themes/custom/amp
npm run install-tools
echo -en "End - Installing npm for amp themes"
echo -en "travis_fold:end:FE-Setup"
