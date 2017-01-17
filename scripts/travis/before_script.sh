#!/usr/bin/env bash

# Clear drush release history cache, to pick up new releases.
rm -f ~/.drush/cache/download/*---updates.drupal.org-release-history-*
# Verify that no git diffs (caused by line ending variation) exist.
# - git diff --exit-code
# The local.hostname must be set to 127.0.0.1:8888 because we are using drush runserver to run the site on Travis CI.
yaml-cli update:value blt/project.yml project.local.hostname '127.0.0.1:8888'
