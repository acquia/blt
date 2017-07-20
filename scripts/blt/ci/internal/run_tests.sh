#!/usr/bin/env bash

set -ev

yaml-cli update:value blt/project.yml project.local.hostname '127.0.0.1:8888'

# Build codebase, validate, install Drupal, run basic tests.
yaml-cli update:value blt/project.yml cm.strategy none
blt validate:all
blt setup --define environment=ci --yes -vvv
blt tests:all --define tests.run-server=true --yes -vvv
blt tests:behat:definitions
drush config-export --root=docroot -y

# Test core-only config management.
yaml-cli update:value blt/project.yml cm.strategy core-only
drush config-export --root=docroot -y
blt setup:config-import

# Test features config management.
yaml-cli update:value blt/project.yml cm.strategy features
rm -rf config/default/*
drush en features --root=docroot -y
drush config-export --root=docroot -y
blt setup:config-import
drush pm-uninstall features --root=docroot -y

# Test config split.
yaml-cli update:value blt/project.yml cm.strategy config-split
drush en config_split --root=docroot -y
drush config-export --root=docroot -y
mv ${BLT_DIR}/scripts/blt/ci/internal/config_split.config_split.ci.yml config/default/
blt setup:config-import
drush pm-uninstall config_split --root=docroot -y

# Remove all exported configuration from previous test steps.
rm -rf config/default/*

# Test deployment commands.
blt deploy:update

# Test SimpleSAMLphp configuration.
blt simplesamlphp:init

# Test that custom commands are loaded.
blt custom:hello

# Run the doctor.
blt doctor

set +v
