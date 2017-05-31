#!/usr/bin/env bash

set -ev

export PATH=${COMPOSER_BIN}:$PATH

# Ensure code quality of 'blt' itself.
${BLT_DIR}/vendor/bin/robo sniff-code

set +v
