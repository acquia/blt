#!/bin/bash
# Common functions for cloud hooks.

status=0

deploy_updates() {

  echo "Running updates for environment: $target_env"

  # Prep for BLT commands.
  repo_root="/var/www/html/$site.$target_env"
  export PATH=$repo_root/vendor/bin:$PATH
  cd $repo_root

  blt deploy:update -Denvironment=$target_env
  if [ $? -ne 0 ]; then
      echo "Update errored."
      status=1;
  fi

  echo "Finished updates for environment: $target_env"
}

deploy_install() {

  echo "Installing site for environment: $target_env"

  # Prep for BLT commands.
  repo_root="/var/www/html/$site.$target_env"
  export PATH=$repo_root/vendor/bin:$PATH
  cd $repo_root

  blt deploy:drupal:install -Denvironment=$target_env
  if [ $? -ne 0 ]; then
      echo "Install errored."
      status=1;
  fi

  echo "Finished installing for environment: $target_env"
}
