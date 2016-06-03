#!/usr/bin/env bash

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

# Update current database to reflect the state of the Drupal file system
$DIR/../../bolt.sh ci:update
