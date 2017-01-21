#!/usr/bin/env bash

# On OSX, create ~/.bash_profile if it does not exist.
if [[ "$OSTYPE" == "darwin"* ]]; then
  touch $HOME/.bash_profile;
fi

if [ "`basename "/$SHELL"`" = "zsh" ]; then
  DETECTED_PROFILE="$HOME/.zshrc"
elif [ -f "$HOME/.bash_profile" ]; then
  DETECTED_PROFILE="$HOME/.bash_profile"
elif [ -f "$HOME/.bashrc" ]; then
  DETECTED_PROFILE="$HOME/.bashrc"
elif [ -f "$HOME/.profile" ]; then
  DETECTED_PROFILE="$HOME/.profile"
fi

if [ ! -z "$DETECTED_PROFILE" ]; then
  if [ "`grep 'function blt' $DETECTED_PROFILE`" ]; then

    if [ "`grep 'GIT_ROOT/blt.sh' $DETECTED_PROFILE`" ]; then
      echo "You have an outdated version of the blt alias installed."
      echo "Please remove it from $DETECTED_PROFILE and re-run this command."
      exit 1
    fi

    echo "Alias for blt already exists in $DETECTED_PROFILE"
    exit
  fi

  while getopts ":y" arg; do
  case $arg in
    y)
      REPLY=y
      ;;
    esac
  done

  echo ""
  echo "BLT can automatically create a Bash alias to make it easier to run BLT tasks."
  echo "This alias may be created in .bash_profile or .bashrc depending on your system architecture."
  echo ""
  sleep 1

  if [ -z $REPLY ]; then
    read -p "Install alias? (y/n)" -n 1 -r
    echo ""
  fi

  if [[ $REPLY =~ ^[Yy]$ ]]; then
    DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

    if cat $DIR/alias >> $DETECTED_PROFILE; then
      echo "Added alias for blt to $DETECTED_PROFILE"
      echo "You may now use the 'blt' command from anywhere within a BLT-generated repository."
      echo ""
      echo "Restart your terminal session or run 'source $DETECTED_PROFILE' to use the new command."
      exit
    else
      echo "Error: Could not modify $DETECTED_PROFILE."
      exit 1
    fi

  fi

else
  echo "Could not install blt alias. No profile found. Tried ~/.zshrc, ~/.bashrc, ~/.bash_profile and ~/.profile."
  exit 1
fi
