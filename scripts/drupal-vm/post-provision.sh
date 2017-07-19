#!/bin/bash

# Add blt alias to front of .bashrc so that it applies to non-interactive shells.
BLT_ALIAS_FILE="/vagrant/vendor/acquia/blt/scripts/blt/alias"
if [ -f "$BLT_ALIAS_FILE" ]
then
    grep -q -F 'function blt' /home/vagrant/.bashrc || (cat "$BLT_ALIAS_FILE" /home/vagrant/.bashrc > temp && mv temp /home/vagrant/.bashrc)
    chown vagrant /home/vagrant/.bashrc
    chgrp vagrant /home/vagrant/.bashrc

else
    echo "Make sure you're in the project root and have run composer install."
    exit 1
fi
