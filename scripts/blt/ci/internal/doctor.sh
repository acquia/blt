#!/usr/bin/env bash

set -ev

# Require ACSF directly to prevent memory exhaustion error. We do this rather than calling acsf:init.
composer require drupal/acsf:^1.33.0
# Initialize ACSF config.
blt acsf:init:hooks
blt acsf:init:drush
blt doctor
# Add Drupal VM config to repo.
blt vm -Dvm.boot='false'

set +v
