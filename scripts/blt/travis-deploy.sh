#!/usr/bin/env bash

set -x

# This script performs a deployment for BLT itself via Travis CI.

# Deploy to Acquia Cloud
blt deploy -Ddeploy.commitMsg="Automated commit by Travis CI for Build ${TRAVIS_BUILD_ID}" -Ddeploy.branch="8.x-build"
# Execute functional tests to assert that deployment artifact was created correctly.
phpunit tests/phpunit --group=deploy

# Execute Pipelines build.
# N3_KEY and N3_SECRET are Travis CI environmental variables.
mv $TRAVIS_BUILD_DIR/scripts/blt/acquia-credentials ~/.acquia/pipelines/credentials
cd deploy
curl -o pipelines https://cloud.acquia.com/pipeline-client/download
chmod a+x pipelines
./pipelines start
