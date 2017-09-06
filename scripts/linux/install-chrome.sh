#!/usr/bin/env bash
# Installs chromedriver and chrome for Linux 64 bit systems.
# Requires Trusty or higher.
# example usage `./install-chrome.sh usr/local/bin`

# Scripts expect argument specifying bin dir.
BIN_DIR=$1

if [ "$(expr substr $(uname -s) 1 5)" == "Linux" ]; then
  # Download google chrome.
  # wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
  # sudo dpkg -i --force-depends google-chrome-stable_current_amd64.deb
  # apt-get -f install

  # Installs chromedriver for Linux 64 bit systems.
  LATEST_CHROMEDRIVER=$(wget -q -O - http://chromedriver.storage.googleapis.com/LATEST_RELEASE)
  wget -N http://chromedriver.storage.googleapis.com/$LATEST_CHROMEDRIVER/chromedriver_linux64.zip
  unzip chromedriver_linux64.zip
  chmod +x chromedriver
  mv -f chromedriver $BIN_DIR
  rm chromedriver_linux64.zip
fi
