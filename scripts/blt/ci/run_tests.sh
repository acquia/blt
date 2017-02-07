#!/usr/bin/env bash

set -ev

${COMPOSER_BIN}/phpunit ${BLT_DIR}/tests/phpunit --group blt-project

# Validate composer.json file.
composer validate --no-check-all --ansi

#  The local.hostname must be set to 127.0.0.1:8888 because we are using drush runserver to test the site.
yaml-cli update:value blt/project.yml project.local.hostname '127.0.0.1:8888'

set +v
