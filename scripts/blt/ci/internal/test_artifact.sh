#!/usr/bin/env bash

set -ev

# Run 'blt' phpunit tests, excluding deploy-push tests.
phpunit ${BLT_DIR}/tests/phpunit --group blt --exclude-group deploy-push

set +v
