#!/usr/bin/env bash

set -ev

cd ${TRAVIS_BUILD_DIR}/../blt-project

# git.remotes already defined in create_blt_project.sh.
yaml-cli update:value .travis.yml addons.ssh_known_hosts.0 svn-5223.devcloud.hosting.acquia.com
yaml-cli update:value .travis.yml before_deploy.0 'openssl aes-256-cbc -K $encrypted_065fa5839cf8_key -iv $encrypted_065fa5839cf8_iv -in id_rsa_blted8.enc -out ~/.ssh/id_rsa -d; chmod 600 ~/.ssh/id_rsa; eval "$(ssh-agent -s)"; ssh-add ~/.ssh/id_rsa;'
cp ${TRAVIS_BUILD_DIR}/id_rsa_blted8.enc .

# Remove the symlink definition for BLT from composer.json.
composer config --unset repo.blt
composer require acquia/blt:8.x-dev#${TRAVIS_COMMIT}
composer update --lock
git remote add github git@github.com:acquia-pso/blted8.git
echo "[![Build Status](https://travis-ci.org/acquia-pso/blted8.svg?branch=8.x)](https://travis-ci.org/acquia-pso/blted8)" >> README.md

git add -A
git commit -m "Automated commit for BLT repo by Travis CI for Build ${TRAVIS_BUILD_ID}" -n
git checkout -b ${TRAVIS_BRANCH}
git push github ${TRAVIS_BRANCH} -f

tag_name=${TRAVIS_BRANCH}.${TRAVIS_COMMIT}
git tag ${tag_name}
git push github ${tag_name}

set +v
