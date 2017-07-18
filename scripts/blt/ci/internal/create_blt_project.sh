#!/usr/bin/env bash

# This script is used for internal testing of BLT.
# It will generate a sibling directory for BLT named `blt-project`.
# The new sample project will have a symlink to BLT in blt-project/vendor/acquia/blt.

set -ev

export PATH=${COMPOSER_BIN}:${PATH}

# Generate a new 'blt-project' project.
cp -R blt-project ../
cd ../blt-project
git init
git add -A
# Commit so that subsequent git commit tests have something to amend.
git commit -m 'Initial commit.'

# Replace '../blt' with the absolute path to BLT.
sed -i "s:\.\./blt:${BLT_DIR}:g" composer.json

# BLT is the only dependency at this point. Install it.
composer install
export PATH=${BLT_DIR}/../blt-project/vendor/bin:$PATH
# The local.hostname must be set to 127.0.0.1:8888 because we are using drush runserver to run the site on Travis CI.
yaml-cli update:value blt/project.yml project.local.hostname '127.0.0.1:8888'
# Define BLT's deployment endpoints.
yaml-cli update:value blt/project.yml git.remotes.0 bolt8@svn-5223.devcloud.hosting.acquia.com:bolt8.git
yaml-cli update:value blt/project.yml git.remotes.1 git@github.com:acquia-pso/blted8.git
# Set cm.core.deploy-key to sync since we are not executing this on Acquia Cloud.
touch blt/project.local.yml
yaml-cli update:value blt/project.local.yml cm.core.deploy-key sync

git add -A
git commit -m 'Adding new dependencies from BLT update.' -n
# Create a .travis.yml, just to make sure it works. It won't be executed.
blt ci:travis:init
# Remove call to exit_early script.
yaml-cli update:value .travis.yml before_install.4 ''

blt ci:pipelines:init
git add -A
git commit -m 'Initializing Travis CI and Acquia Pipelines.' -n
# Disable Lightning tests on pull requests.
# 'if [ "$PULL_REQUEST" != "false" ]; then printf "behat.paths: [ \${repo.root}/tests/behat ]" >> blt/project.yml; fi'

# Initialize ACSF config.
blt acsf:init -y
# Initialize Acquia Cloud hooks.
blt setup:cloud-hooks
# Change cloud hooks to re-install Drupal on deployments.
sed -i "s:deploy_updates:deploy_install:g" hooks/common/post-code-deploy/post-code-deploy.sh
sed -i "s:deploy_updates:deploy_install:g" hooks/common/post-code-update/post-code-update.sh

# Create example command and hook files.
blt examples:init

# Dump all config values.
blt config:dump

set +v
