#!/usr/bin/env bash
# Installs chromedriver for Linux 64 bit systems.
# Requires Trusty or higher.
# example usage `./chromedriver.sh usr/local/bin`

# Scripts expect argument specifying bin dir.
BIN_DIR=$1

if [ "$(expr substr $(uname -s) 1 5)" == "Linux" ]; then
  set -x

  # Download google chrome.
  wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
  sudo dpkg -i --force-depends google-chrome-stable_current_amd64.deb

  # Download and install chromedriver.
  LATEST=$(wget -q -O - http://chromedriver.storage.googleapis.com/LATEST_RELEASE)
  wget http://chromedriver.storage.googleapis.com/$LATEST/chromedriver_linux64.zip
  unzip chromedriver_linux64.zip
  chmod +x chromedriver
  mv -f chromedriver $BIN_DIR
fi
