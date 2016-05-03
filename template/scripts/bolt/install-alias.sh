#!/usr/bin/env bash

if [ -f ~/.bashrc ]; then
  if [ ! "`grep 'function bolt' ~/.bashrc`" ]; then
    # Check for aliases in old-style .bash_profile.
    # This check can be removed after everyone has moved to .bashrc.
    if [ -f ~/.bash_profile ]; then
      if [ "`grep 'function bolt' ~/.bash_profile`" ]; then
        echo "Alias for bolt already exists in ~/.bash_profile"
        exit
      fi
    fi
    DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
    cat $DIR/alias >> ~/.bashrc
    echo "Added alias for bolt to ~/.bashrc."
    echo "Restart your terminal session to use the new command."
  else
    echo "Alias for bolt already exists in ~/.bashrc"
  fi
else
  echo "~/.bashrc was not found. Could not install bolt alias."
fi
