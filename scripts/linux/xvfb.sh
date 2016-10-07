#!/usr/bin/env bash

# Install and start xvfb.
# @see http://tobyho.com/2015/01/09/headless-browser-testing-xvfb/
sudo apt-get install xvfb
export DISPLAY=:99.0
sh -e /etc/init.d/xvfb start
