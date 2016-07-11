#!/usr/bin/env bash

echo "Note that you will lose any custom scripts in build/custom"

# Move values from custom/build.yml to project.yml.
cat build/custom/phing/build.yml >> project.yml
# Remove unneeded files.
rm -rf build bolt.sh tests/phpunit/blt
# @todo remove old alias!
# Install (new) alias
./vendor/acquia/blt/blt.sh install-alias
blt init
blt configure

# Move build/custom/files to new locations (e.g., deploy excludes or .gitignores).
