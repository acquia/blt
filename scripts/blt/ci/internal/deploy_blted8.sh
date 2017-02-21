#!/usr/bin/env bash

set -ev

cd ${TRAVIS_BUILD_DIR}/../blt-project
# Remove the symlink definition for BLT from composer.json.
composer config --unset repo.blt
composer require acquia/blt:8.x-dev#${TRAVIS_COMMIT}
composer update --lock
git remote add github git@github.com:acquia-pso/blted8.git

echo "[![Build Status](https://travis-ci.org/acquia-pso/blted8.svg?branch=master)](https://travis-ci.org/acquia-pso/blted8)" >> README.md

git add -A
git commit -m "Automated commit by Travis CI for Build ${TRAVIS_BUILD_ID}" -n
git checkout -b ${TRAVIS_BRANCH}
git push github ${TRAVIS_BRANCH} -f

tag_name=${TRAVIS_BRANCH}.${TRAVIS_COMMIT}
git tag ${tag_name}
git push github ${tag_name}

set +v
