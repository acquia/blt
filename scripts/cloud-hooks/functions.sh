#!/bin/bash
# Common functions for cloud hooks.

status=0

deploy_updates() {

  echo "Running updates for environment: $target_env"

  # Prep for BLT commands.
  repo_root="/var/www/html/$site.$target_env"
  export PATH=$repo_root/vendor/bin:$PATH
  cd $repo_root

  blt deploy:update
  if [ $? -ne 0 ]; then
      echo "Update errored."
      status=1;
  fi

  echo "Finished updates for environment: $target_env"
}

# TODO: Add a deploy_install command for projects early in development that want
# to reinstall on every deploy.
