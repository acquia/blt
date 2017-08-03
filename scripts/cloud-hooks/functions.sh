#!/bin/bash
# Common functions for cloud hooks.

status=0

drush_alias=${site}'.'${target_env}

deploy_updates() {

  case $target_env in
    01dev|01test|01live)
      acsf_deploy
      ;;
    01devup|01testup|01update)
      ;;
    *)
      ace_deploy
      ;;
    esac
}

acsf_deploy() {
  sites=()
  # Prep for BLT commands.
  repo_root="/var/www/html/$site.$target_env"
  export PATH=$repo_root/vendor/bin:$PATH
  cd $repo_root

  echo "Running updates for environment: $target_env"

  # Generate an array of all site URIs on the Factory from parsed output of Drush utility.
  while IFS=$'\n' read -r line; do
      sites[i++]="$line"
      done < <(drush @"${drush_alias}" --include=./drush acsf-tools-list | grep domains: -A 1 | grep 0: | sed -e 's/^[0: ]*//')
      unset IFS

  # Loop through each available site uri and run BLT deploy updates.
  for uri in "${sites[@]}"; do
  #Override BLT default deploy uri.
  blt deploy:update --define environment=$target_env --define drush.uri="$uri" -v -y
  if [ $? -ne 0 ]; then
      echo "Update errored for site $uri."
      exit 1
  fi

  echo "Finished updates for site: $uri."
  done

  echo "Finished updates for all $target_env sites."
}

ace_deploy() {

  echo "Running updates for environment: $target_env"

  # Prep for BLT commands.
  repo_root="/var/www/html/$site.$target_env"
  export PATH=$repo_root/vendor/bin:$PATH
  cd $repo_root

  blt deploy:update --define environment=$target_env -v -y
  if [ $? -ne 0 ]; then
      echo "Update errored."
      exit 1
  fi

  echo "Finished updates for environment: $target_env"
}

deploy_install() {

  echo "Installing site for environment: $target_env"

  # Prep for BLT commands.
  repo_root="/var/www/html/$site.$target_env"
  export PATH=$repo_root/vendor/bin:$PATH
  cd $repo_root

  blt deploy:drupal:install --define environment=$target_env -v -y
  if [ $? -ne 0 ]; then
      echo "Install errored."
      exit 1
  fi

  echo "Finished installing for environment: $target_env"
}
