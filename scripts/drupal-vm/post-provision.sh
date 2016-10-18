#!/usr/bin/env bash

# This command is run as sudo, '~' will expand to '/root'.
VAGRANT_MACHINE_NAME=$(grep vagrant_machine_name: /vagrant/box/config.yml | cut -d' ' -f 2)
REPO_ROOT=/var/www/${VAGRANT_MACHINE_NAME}
cd ${REPO_ROOT}

# Add blt alias to front of .bashrc so that it applies to non-interactive shells.
grep -q -F 'blt' /home/vagrant/.bashrc || (cat ./vendor/acquia/blt/scripts/blt/alias /home/vagrant/.bashrc > temp && mv temp /home/vagrant/.bashrc)
