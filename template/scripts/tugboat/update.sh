#!/usr/bin/env bash

# Import config
cd /var/lib/tugboat/docroot
chown -R www-data sites/default/files

# Update current database to reflect the state of the Drupal file system
$DIR/../../blt.sh ci:update
