#!/usr/bin/env bash

# This script is used for internal testing of BLT.
# It will generate a sibling directory for BLT named `blt-project`.
# The new sample project will have a symlink to BLT in blt-project/vendor/acquia/blt.

set -ev

export PATH=${COMPOSER_BIN}:${PATH}

${BLT_DIR}/vendor/bin/robo create:symlinked-project
cd ../blted8

export PATH=${BLT_DIR}/../blt-project/vendor/bin:$PATH

# The local.hostname must be set to 127.0.0.1:8888 because we are using drush runserver to run the site on Travis CI.
yaml-cli update:value blt/project.yml project.local.hostname '127.0.0.1:8888'

# Define BLT's deployment endpoints.
yaml-cli update:value blt/project.yml git.remotes.0 bolt8@svn-5223.devcloud.hosting.acquia.com:bolt8.git
yaml-cli update:value blt/project.yml git.remotes.1 git@github.com:acquia-pso/blted8.git
