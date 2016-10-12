#!/usr/bin/env bash

# Append vendor/bin to $PATH via ~/.bashrc modification.
grep -q -F 'export PATH=/var/www/$(hostname -d | cut -d"." -f 1)/vendor/bin:$PATH' .bashrc || echo 'export PATH=/var/www/$(hostname -d | cut -d"." -f 1)/vendor/bin:$PATH' >> .bashrc
