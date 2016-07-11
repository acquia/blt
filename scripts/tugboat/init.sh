#!/usr/bin/env bash

DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
DOCROOT=/var/lib/tugboat/docroot

mysql -h mysql -u tugboat -ptugboat -e 'CREATE DATABASE drupal;'
composer selfupdate
composer install

# Install nvm and specified nodejs version.
$DIR/install-node.sh 4.4.1


$DIR/../../blt.sh ci:setup
