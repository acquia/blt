#!/usr/bin/env bash

set -ev

export PATH=${COMPOSER_BIN}:$PATH

# Ensure code quality of 'blt' itself.
phpcs --standard=${BLT_DIR}/vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml tests

set +v
