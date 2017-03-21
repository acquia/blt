#!/usr/bin/env bash

set -ev

# Require ACSF directly to prevent memory exhaustion error. We do this rather than calling acsf:init.
composer require drupal/acsf:^1.33.0
# Initialize ACSF config.
blt acsf:init:hooks
# Initialize Acquia Cloud hooks.
blt setup:cloud-hooks
# Change cloud hooks to re-install Drupal on deployments.
sed -i "s:deploy_updates:deploy_install:g" hooks/common/post-code-deploy/post-code-deploy.sh
sed -i "s:deploy_updates:deploy_install:g" hooks/common/post-code-update/post-code-update.sh

blt acsf:init:drush
blt doctor
# Add Drupal VM config to repo.
blt vm -Dvm.boot='false'

set +v
