#!/usr/bin/env bash

if [ "`basename "/$SHELL"`" = "zsh" ]; then
  DETECTED_PROFILE="$HOME/.zshrc"
elif [ -f "$HOME/.bashrc" ]; then
  DETECTED_PROFILE="$HOME/.bashrc"
elif [ -f "$HOME/.bash_profile" ]; then
  DETECTED_PROFILE="$HOME/.bash_profile"
elif [ -f "$HOME/.profile" ]; then
  DETECTED_PROFILE="$HOME/.profile"
fi

if [ ! -z "$DETECTED_PROFILE" ]; then
  if [ "`grep 'function blt' $DETECTED_PROFILE`" ]; then
    echo "Alias for blt already exists in $DETECTED_PROFILE"
    exit
  fi
  DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
  cat $DIR/alias >> $DETECTED_PROFILE

  echo "Added alias for blt to $DETECTED_PROFILE"
  echo "Restart your terminal session to use the new command."
else
  echo "Could not install blt alias. No profile found. Tried ~/.zshrc, ~/.bashrc, ~/.bash_profile and ~/.profile."
fi
