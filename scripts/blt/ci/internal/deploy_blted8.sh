#!/usr/bin/env bash

set -ev

cd ${TRAVIS_BUILD_DIR}/../blt-project
git remote add github git@github.com:acquia-pso/blted8.git
git add -A
git commit -m "Automated commit by Travis CI for Build ${TRAVIS_BUILD_ID}" -n
git checkout -b ${TRAVIS_BRANCH}
git push github ${TRAVIS_BRANCH} -f

set +v
