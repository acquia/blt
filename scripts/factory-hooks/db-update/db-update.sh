#!/bin/bash
#
# Factory Hook: db-update
#
# The existence of one or more executable files in the
# /factory-hooks/db-update directory will prompt them to be run *instead of* the
# regular database update (drush updatedb) command. So that update command will
# normally be part of the commands executed below.
#
# Usage: post-code-deploy site env db-role domain custom-arg
# Map the script inputs to convenient names.
# Acquia hosting site / environment names
site="$1"
env="$2"
# database role. (Not expected to be needed in most hook scripts.)
db_role="$3"
# The public domain name of the website.
domain="$4"

# BLT executable:
blt="/var/www/html/$site.$env/vendor/acquia/blt/bin/blt"

# You need the URI of the site factory website in order for drush to target that
# site. Without it, the drush command will fail. Use the uri.php file provided by the acsf module to
# locate the URI based on the site, environment and db role arguments.
uri=`/usr/bin/env php /mnt/www/html/$site.$env/hooks/acquia/uri.php $site $env $db_role`

# Print a statement to the cloud log.
echo "$site.$target_env: Running BLT deploy tasks on $uri domain in $env environment on the $site subscription."

IFS='.' read -a name <<< "${uri}"

# Set Drush cache to local ephemeral storage to avoid race conditions. This is
# done on a per site basis to completely avoid race conditions.
# @see https://github.com/acquia/blt/pull/2922
export DRUSH_PATHS_CACHE_DIRECTORY=/tmp/.drush/${db_role}

$blt drupal:update --environment=$env --site=${name[0]} --define drush.uri=$domain --verbose --yes
