#!/usr/bin/env bash

# Update blt symlink prior to deploy. Changing from ../blt to ../../blt.
sed -i 's/\.\.\/blt/\.\.\/\.\.\/blt/g' composer.json
sed -i 's/\.\.\/blt/\.\.\/\.\.\/blt/g' composer.lock

# From this point, the CI-specific build (e.g., .travis.yml or acquia-pipelines.yml) should create artifact.
