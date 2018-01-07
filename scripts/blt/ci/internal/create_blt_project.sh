#!/usr/bin/env bash

# This script is used for internal testing of BLT.
# It will generate a sibling directory for BLT named `blted8`.
# The new sample project will have a symlink to BLT in blted8/vendor/acquia/blt.

set -ev

export PATH=${COMPOSER_BIN}:${PATH}

${BLT_DIR}/vendor/bin/robo create:from-symlink --no-vm

if [ "${DRUPAL_CORE_VERSION}" != "default" ]; then
  composer require "drupal/core:${DRUPAL_CORE_VERSION}" --no-update --no-interaction
  composer update --no-interaction
fi

export PATH=${BLT_DIR}/../blted8/vendor/bin:$PATH

# Define BLT's deployment endpoints.
yaml-cli update:value ../blted8/blt/project.yml git.remotes.0 bolt8@svn-5223.devcloud.hosting.acquia.com:bolt8.git
yaml-cli update:value ../blted8/blt/project.yml git.remotes.1 git@github.com:acquia-pso/blted8.git
