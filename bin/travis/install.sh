#!/usr/bin/env bash

# NAME
#     install.sh - Install Travis CI dependencies
#
# SYNOPSIS
#     install.sh
#
# DESCRIPTION
#     Creates the test fixture.

cd "$(dirname "$0")"; source _includes.sh

composer -d${BLT_DIR} install

source ${BLT_DIR}/scripts/travis/setup_environment

# Create extra dbs for multisite testing.
mysql -u root -e "CREATE DATABASE drupal2; GRANT ALL ON drupal2.* TO 'drupal'@'localhost';"
mysql -u root -e "CREATE DATABASE drupal3; GRANT ALL ON drupal3.* TO 'drupal'@'localhost';"
mysql -u root -e "CREATE DATABASE drupal4; GRANT ALL ON drupal4.* TO 'drupal'@'localhost';"
