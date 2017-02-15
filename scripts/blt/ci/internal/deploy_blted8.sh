#!/usr/bin/env bash

cd ${TRAVIS_BUILD_DIR}/../blt-project
git remote add github https://github.com/acquia-pso/blted8.git
git push github 8.x -f
