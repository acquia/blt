#!/bin/bash

# Work around a problem where PHPStan doesn't have access to global Composer
# classes, and thus errors when trying to scan BLT's composer plugin file.

source ../orca/bin/travis/_includes.sh

if [[ "$ORCA_JOB" = "DEPRECATED_CODE_SCAN" ]]; then
  composer --working-dir="$ORCA_FIXTURE_DIR" require composer/composer
fi
