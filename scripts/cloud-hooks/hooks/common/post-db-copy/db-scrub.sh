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
  echo "$site.$target_env: Scrubbing database $db_name"
  drush @$site.$target_env sql-sanitize --yes
  drush @$site.$target_env cache-rebuild
fi

set +v
