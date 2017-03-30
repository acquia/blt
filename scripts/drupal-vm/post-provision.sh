#!/bin/bash

# This command is run as sudo, '~' will expand to '/root'.
CONFIG_FILE="/vagrant/box/config.yml"
if [ -f "$CONFIG_FILE" ]
then
    VAGRANT_MACHINE_NAME=$(grep vagrant_machine_name: "$CONFIG_FILE" | cut -d' ' -f 2)
    REPO_ROOT=/var/www/${VAGRANT_MACHINE_NAME}
    cd ${REPO_ROOT}
fi

# Add blt alias to front of .bashrc so that it applies to non-interactive shells.
BLT_ALIAS_FILE="./vendor/acquia/blt/scripts/blt/alias"
if [ -f "$BLT_ALIAS_FILE" ]
then
    grep -q -F 'function blt' /home/vagrant/.bashrc || (cat "$BLT_ALIAS_FILE" /home/vagrant/.bashrc > temp && mv temp /home/vagrant/.bashrc)
else
    echo "Make sure you're in the project root and have run composer install."
    exit 1
fi
