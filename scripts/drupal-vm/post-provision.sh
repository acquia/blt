#!/usr/bin/env bash

# Append vendor/bin to $PATH via ~/.bashrc modification.
grep -q -F 'export PATH=/var/www/$(hostname)/vendor/bin:$PATH' .bashrc || echo 'export PATH=/var/www/$(hostname)/vendor/bin:$PATH' >> .bashrc
