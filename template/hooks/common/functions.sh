#!/bin/bash
# Common functions for cloud hooks.

status=0

env_refresh() {

  echo "Running env_refresh() for environment: $target_env"

  #create drush command
  drush_cmd="drush @$site.$target_env"
  echo $drush_cmd

  # Prep for BLT commands.
  repo_root="/var/www/html/$site.$target_env"
  export PATH=$repo_root/vendor/bin:$PATH
  cd $repo_root

  echo "Update starting..."
  blt deploy:update
  if [ $? -ne 0 ]; then
      echo "Update errored."
      status=1;
  fi
  echo "Updates complete."

  # if [ "$target_env" != "prod" ]; then
  #   echo "Enabling devel modules..."
  #   $drush_cmd pm-enable -y lightning_devel
  #   if [ $? -ne 0 ]; then
  #     echo "Devel module enable errored."
  #     status=1;
  #   fi
  #   echo "Enabled devel module."
  # fi

  echo "Finished env_refresh() for environment: $target_env"
}
