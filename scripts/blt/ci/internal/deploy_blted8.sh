#!/usr/bin/env bash

set -ev

cd ${TRAVIS_BUILD_DIR}/../blted8

git remote add github git@github.com:acquia-pso/blted8.git
git checkout -b ${TRAVIS_BRANCH}
git push github ${TRAVIS_BRANCH} -f

tag_name=${TRAVIS_BRANCH}.${TRAVIS_COMMIT}
git tag ${tag_name}
git push github ${tag_name}

set +v
