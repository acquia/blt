#!/bin/sh
#
# db-copy Cloud hook: db-scrub
#
# Scrub important information from a Drupal database.
#
# Usage: db-scrub.sh site target-env db-name source-env

set -ev

site="$1"
target_env="$2"
db_name="$3"
source_env="$4"

acsf_file="/mnt/files/$AH_SITE_GROUP.$AH_SITE_ENVIRONMENT/files-private/sites.json"
if [ ! -f $acsf_file ]; then
  # Prep for BLT commands.
  repo_root="/var/www/html/$site.$target_env"
  export PATH=$repo_root/vendor/bin:$PATH
  cd $repo_root

  blt artifact:ac-hooks:db-scrub $site $target_env $db_name $source_env

fi

set +v
