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
    echo "Alias for blt exists in $DETECTED_PROFILE"
    echo "Please remove it and then re-run this script."
    exit 1
  fi
fi

echo "This script will update your project to use a composerized version of BLT."
echo "It will do the following:"
echo "* Add a blt alias to $DETECTED_PROFILE"
echo "* Remove and modify files in your codebase."
echo "* Update your composer dependencies."
echo ""
read -p "Continue? (y/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
  # composer require acquia/blt:~8

  # Move values from custom/build.yml to blt.yml.
  # @todo Check if this exists and if it exactly matches core value.
  if [[ ! -z build/custom/phing/build.yml ]]; then
    echo "Moving custom Phing properties to blt.yml."
    echo "" >> blt.yml
    cat build/custom/phing/build.yml >> blt.yml
  fi

  # Move build/custom/files to new locations (e.g., deploy excludes or .gitignores).
  echo "Moving custom Phing files from build/custom to blt."
  mkdir blt
  mv build/custom blt


  # Remove unneeded files.
  ./vendor/acquia/blt/scripts/blt/update.sh

  # Install (new) alias
  echo "Installing blt alias"
  yes | ./vendor/acquia/blt/blt.sh blt:init:shell-alias
  ./vendor/acquia/blt/blt.sh init
  composer update

  echo "Update complete. Please do the following:"
  echo ""
  echo "* Restart your terminal session to register your new blt alias."
  echo "* Review your codebase and commit the desired changes."
  echo "    * If you have a custom Phing build file, you will likely need to update it and add it to blt.yml under the 'import' key. See readme/extending-blt.md."
  echo "    * If you had custom files in docroot/sites/default/settings, you will need to restore them."
  echo "    * If you are not using Lightning, remove lightning-specific command-hooks from blt.yml."
  # link to online docs

  ./vendor/bin/drupal yaml:get:value blt.yml project.hash_salt > salt.txt
  # remove project.hash_salt, project.themes, project.vendor from blt.yml

else
  exit 1
fi

