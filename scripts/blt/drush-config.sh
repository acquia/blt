#!/usr/bin/env bash

REPOROOT="$1"

DRUSH8="\"$REPOROOT/vendor-bin/drush-8/vendor/bin/drush\""

DRUSH9="\"$REPOROOT/vendor-bin/drush-9/vendor/bin/drush\""

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
  if [ "`grep 'BLT GENERATED DRUSH SCRIPT PATH ALIASES' $DETECTED_PROFILE`" ]; then
    echo "You have existing drush executable overrides installed."
    echo "Remove modifications and run this task agin to generate new $DETECTED_PROFILE config."
    exit
    fi

    echo "Writing config to $DETECTED_PROFILE"

    echo "#BLT GENERATED DRUSH SCRIPT PATH ALIASES" >> $DETECTED_PROFILE
    echo "export DRUSH_LAUNCHER_FALLBACK="$DRUSH8"" >> $DETECTED_PROFILE
    echo "alias drush8="$DRUSH8"" >> $DETECTED_PROFILE
    echo "alias drush9="$DRUSH9"" >> $DETECTED_PROFILE
    echo "alias drush=drush9" >> $DETECTED_PROFILE
    echo "Added drush script aliases to $DETECTED_PROFILE. "
    echo "Running 'source $DETECTED_PROFILE to persist changes across terminal sessions.'"

  if source $DETECTED_PROFILE; then
    echo "Drush executables successfully configured in $DETECTED_PROFILE"
      exit
      else
      echo "Error: Could not modify $DETECTED_PROFILE."
      exit 1
    fi

else
  echo "Could not save configuration. No profile found. Tried ~/.zshrc, ~/.bashrc, ~/.bash_profile and ~/.profile."
  exit 1
fi
