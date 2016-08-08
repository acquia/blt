#!/usr/bin/env bash

# Test if Composer is installed
composer -v > /dev/null 2>&1
COMPOSER_IS_INSTALLED=$?

# True, if composer is not installed
if [[ $COMPOSER_IS_INSTALLED -ne 0 ]]; then
  echo "Composer is required but not found."
  echo "Please install composer from the composer website:"
  echo "  https://getcomposer.org/doc/00-intro.md"
  exit 1
fi

SCRIPT_DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
BIN=$(cd ${SCRIPT_DIR}/../../bin && pwd)
PHING=${BIN}/phing

if [ ! -f ${PHING} ]; then
  echo "Phing was not found in this project's vendor/bin directory."
  echo "Attempting to run composer install. This takes a few minutes."
  composer install
fi

# This script simply passes all arguments to Phing.
${PHING} -f ${SCRIPT_DIR}/phing/build.xml "$@"
