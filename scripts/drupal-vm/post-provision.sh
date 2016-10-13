#!/usr/bin/env bash

set -x
REPO_ROOT=/var/www/$(hostname -d | cut -d"." -f 1)
cd $REPO_ROOT

# Add blt alias to front of .bashrc
grep -q -F 'blt' ~/.bashrc || (cat ./vendor/acquia/blt/scripts/blt/alias ~/.bashrc > temp && mv temp ~/.bashrc)
