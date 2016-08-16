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

  # Move values from custom/build.yml to project.yml.
  # @todo Check if this exists and if it exactly matches core value.
  if [[ ! -z build/custom/phing/build.yml ]]; then
    echo "Moving custom Phing properties to project.yml."
    echo "" >> project.yml
    cat build/custom/phing/build.yml >> project.yml
  fi

  # Move build/custom/files to new locations (e.g., deploy excludes or .gitignores).
  echo "Moving custom Phing files from build/custom to blt."
  mkdir blt
  mv build/custom blt


  # Remove unneeded files.
  echo "Removing deprecated BLT files from project."
  rm -rf build blt.sh tests/phpunit/blt

  # Removing old docs.
  rm readme/acsf-setup.md
  rm readme/architecture.md
  rm readme/best-practices.md
  rm readme/deploy.md
  rm readme/dev-workflow.md
  rm readme/features-workflow.md
  rm readme/local-development.md
  rm readme/onboarding.md
  rm readme/project-tasks.md
  rm readme/release-process.md
  rm readme/repo-architecture.md
  rm readme/views.md

  # Install (new) alias
  echo "Installing blt alias"
  yes | ./vendor/acquia/blt/blt.sh install-alias
  ./vendor/acquia/blt/blt.sh init
  ./vendor/acquia/blt/blt.sh configure
  composer update

  echo "Update complete. Please do the following:"
  echo ""
  echo "* Restart your terminal session to register your new blt alias."
  echo "* Review your codebase and commit the desired changes."
  echo "    * If you have a custom Phing build file, you will likely need to update it and add it to project.yml under the 'import' key. See readme/extending-blt.md."
  echo "    * If you are not using Lightning, remove lightning-specific target-hooks from project.yml."
  # link to online docs

  ./vendor/bin/drupal yaml:get:value project.yml project.hash_salt > salt.txt
  # remove project.hash_salt, project.themes, project.vendor from project.yml

else
  exit 1
fi

