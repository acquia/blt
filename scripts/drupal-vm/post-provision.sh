#!/usr/bin/env bash

set -x
VAGRANT_MACHINE_NAME=$(grep vagrant_machine_name: /vagrant/box/config.yml | cut -d' ' -f 2)
REPO_ROOT=/var/www/${VAGRANT_MACHINE_NAME}
cd ${REPO_ROOT}

# Add blt alias to front of .bashrc
grep -q -F 'blt' ~/.bashrc || (cat ./vendor/acquia/blt/scripts/blt/alias ~/.bashrc > temp && mv temp ~/.bashrc)
