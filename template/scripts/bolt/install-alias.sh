#!/usr/bin/env bash

if [ -f ~/.bash_profile ]; then
  if [ ! "`grep 'function bolt' ~/.bash_profile`" ]; then
     DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
     cat $DIR/alias >> ~/.bash_profile
     echo "Added alias for bolt to ~/.bash_profile."
     echo "Restart your terminal session to use the new command."
  else
    echo "Alias for bolt already exists in ~/.bash_profile"
  fi
else
  echo "~/.bash_profile was not found. Could not install bolt alias."
fi
