#!/usr/bin/env bash

# Usage ./scripts/blt/release-blt 8.6.11

tag="$1"
TOKEN="$2"
branch=$tag-build

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
clear
echo "Make sure that your local LAMP stack is running!"
echo "This will destroy the $(cd $SCRIPT_DIR/../../../blted8 && pwd) directory."
read -p "Press any key to continue."
set -x
cd $SCRIPT_DIR/../../../
export COMPOSER_PROCESS_TIMEOUT=2000
rm -rf blted8
composer create-project acquia/blt-project:8.x-dev blted8 --no-interaction
cd blted8
# Overwrite MySQL creds for your local machine, if necessary.
# echo '$databases["default"]["default"]["username"] = "drupal";' >> docroot/sites/default/settings/local.settings.php
# echo '$databases["default"]["default"]["password"] = "drupal";' >> docroot/sites/default/settings/local.settings.php
./vendor/bin/blt local:setup
cd docroot
drush uli
cd ..
./vendor/bin/blt tests
read -p "Press any key to continue. This will destroy blted8 and re-create it, using a VM this time."
cd ../

rm -rf blted8
mkdir blted8
cd blted8
git init
composer init --stability=dev --no-interaction
composer config prefer-stable true
composer require acquia/blt:8.x-dev
composer update
./vendor/bin/blt vm
./vendor/bin/blt local:setup -v
drush @blted8.local uli
drush @blted8.local ssh blt tests:behat
read -p "Press any key to continue. This will destroy the VM and attempt to perform a Pipelines build."
vagrant destroy

./vendor/bin/yaml-cli update:value git.remotes.0 bolt8pipeline@svn-2420.devcloud.hosting.acquia.com:bolt8pipeline.git
./vendor/bin/blt ci:pipelines:init
git co -b ${branch}
git add -A
git commit -m "Creating test branch for BLT release ${tag}"
git remote add origin bolt8pipeline@svn-2420.devcloud.hosting.acquia.com:bolt8pipeline.git
git push origin ${branch}
pipelines start
