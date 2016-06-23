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

  echo "BLT can automatically create a Bash alias to make it easier to run BLT tasks."
  echo "This alias may be created in .bash_profile or .bashrc depending on your system architecture."
  echo ""
  read -p "Install alias? (y/n)" -n 1 -r
  echo ""

  if [[ $REPLY =~ ^[Yy]$ ]]; then
    DIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )

    if cat $DIR/alias >> $DETECTED_PROFILE; then
      echo "Added alias for blt to $DETECTED_PROFILE"
      echo "You may now use the 'blt' command from anywhere within a BLT-generated repository."
      echo ""
      echo "Restart your terminal session to use the new command."
    else
      echo "Error: Could not modify $DETECTED_PROFILE."
    fi

  fi

else
  echo "Could not install blt alias. No profile found. Tried ~/.zshrc, ~/.bashrc, ~/.bash_profile and ~/.profile."
fi
