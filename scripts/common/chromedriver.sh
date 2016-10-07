#!/usr/bin/env bash

# Scripts expect argument specifying bin dir.
BIN_DIR=$1

if [ "$(expr substr $(uname -s) 1 5)" == "Linux" ]; then
  # Installs chromedriver for Linux 64 bit systems.
  wget -N http://chromedriver.storage.googleapis.com/2.15/chromedriver_linux64.zip
  unzip chromedriver_linux64.zip
  chmod +x chromedriver
  mv -f chromedriver $BIN_DIR
fi
