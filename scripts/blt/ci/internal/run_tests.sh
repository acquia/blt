#!/usr/bin/env bash

set -ev

yaml-cli update:value blt/project.yml project.local.hostname '127.0.0.1:8888'
yaml-cli update:value blt/project.yml modules.ci.enable.0 views_ui

# Build codebase, validate, install Drupal, run basic tests.
yaml-cli update:value blt/project.yml cm.strategy none
blt validate:all
${BLT_DIR}/scripts/travis/tick-tock.sh blt setup --define environment=ci --yes -vvv
blt tests:all --define tests.run-server=true --yes -vvv
blt tests:behat:definitions
cd docroot
drush config-export -y
cd ..

# Test core-only config management.
yaml-cli update:value blt/project.yml cm.strategy core-only
cd docroot
drush config-export -y
cd ..
blt setup:config-import

# Test features config management.
yaml-cli update:value blt/project.yml cm.strategy features
rm -rf config/default/*
cd docroot
drush en features -y
drush config-export -y
cd ..
blt setup:config-import
cd docroot
drush pm-uninstall features -y
cd ..

# Test config split.
yaml-cli update:value blt/project.yml cm.strategy config-split
cd docroot
drush en config_split -y
drush config-export -y
cd ..
mv ${BLT_DIR}/scripts/blt/ci/internal/config_split.config_split.ci.yml config/default/
blt setup:config-import
cd docroot
drush pm-uninstall config_split -y
cd ..

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
