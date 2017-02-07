#!/usr/bin/env bash

set -ev

# Require ACSF directly to prevent memory exhaustion error. We do this rather than calling acsf:init.
composer require drupal/acsf:^1.33.0
# Initialize ACSF config.
blt acsf:init:hooks
blt acsf:init:drush
# Define BLT's deployment endpoints.
yaml-cli update:value blt/project.yml git.remotes.0 bolt8@svn-5223.devcloud.hosting.acquia.com:bolt8.git
yaml-cli update:value blt/project.yml git.remotes.1 git@github.com:acquia-pso/blted8.git
blt doctor
# Add Drupal VM config to repo.
blt vm -Dvm.boot='false'

set +v
