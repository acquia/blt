#!/usr/bin/env bash

# Load composer dependencies.
composer validate --no-check-all --ansi
composer install
export PATH=$TRAVIS_BUILD_DIR/vendor/bin:$PATH
# Install proper version of node for front end tasks.
nvm install 4.4.1
nvm use 4.4.1
# Initialize xvfb (see https://docs.travis-ci.com/user/gui-and-headless-browsers)
export DISPLAY=:99.0
sh -e /etc/init.d/xvfb start
# Installs chromedriver to vendor/bin.
./vendor/acquia/blt/scripts/linux/install-chrome.sh $TRAVIS_BUILD_DIR/vendor/bin
# Use JDK 8.
jdk_switcher use oraclejdk8
