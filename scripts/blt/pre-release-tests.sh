#!/usr/bin/env bash

# Usage ./scripts/blt/release-blt 8.6.11

tag="$1"
branch=${tag}-pre-build
app_id=310109e8-34a7-41ed-86a5-e52f00f2158

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
clear
echo "Make sure that your local LAMP stack is running!"
echo "This will destroy the $(cd $SCRIPT_DIR/../../../blted8 && pwd) directory."
read -p "Press any key to continue."
set -x
cd $SCRIPT_DIR/../../../
export COMPOSER_PROCESS_TIMEOUT=2000
# @todo prompt to delete if exists
if [ -d ./blted8/.vagrant ]; then
  cd blted8
  vagrant destroy
  cd ..
fi
rm -rf blted8
composer create-project acquia/blt-project:8.x-dev blted8 --no-interaction
cd blted8
# Overwrite MySQL creds for your local machine, if necessary.
# echo '$databases["default"]["default"]["username"] = "drupal";' >> docroot/sites/default/settings/local.settings.php
# echo '$databases["default"]["default"]["password"] = "drupal";' >> docroot/sites/default/settings/local.settings.php
./vendor/bin/blt setup
./vendor/bin/blt validate
./vendor/bin/blt examples:init
./vendor/bin/blt tests:all
read -p "Press any key to continue. This will create a VM and re-run tests there. SHUT DOWN MAMP."
./vendor/bin/blt vm --yes
./vendor/bin/blt setup
blt tests:behat --yes
read -p "Press any key to continue. This will destroy the VM and attempt to perform a Pipelines build."
blt vm:nuke

./vendor/bin/yaml-cli update:value blt/project.yml git.remotes.0 bolt8pipeline@svn-2420.devcloud.hosting.acquia.com:bolt8pipeline.git
./vendor/bin/blt ci:pipelines:init
git co -b ${branch}
git add -A
git commit -m "BLT-000: Creating test branch for BLT release ${tag}"
git remote add origin bolt8pipeline@svn-2420.devcloud.hosting.acquia.com:bolt8pipeline.git
git push origin ${branch}
job_id=$(pipelines start | grep "Job ID" | cut -d':' -f2 | awk '{$1=$1;print}')
echo "To check the build status, run:"
echo "pipelines logs --job-id=${job_id} --application-id=${app_id}"

pipelines_branch=pipelines-build-${branch}
# @todo have pipelines deploy and install on ODE.
echo "When the Pipelines build complete, deploy ${pipelines_branch} on an AC environment and re-install Drupal:"
echo "drush @bolt8pipeline.dev ac-code-path-deploy ${pipelines_branch}"
echo "drush @bolt8pipeline.dev si lightning -y"
