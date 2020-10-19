#!/bin/bash
#
# Factory Hook: post-site-install
#
# This is necessary so that blt drupal:install tasks are invoked automatically
# when a site is created on ACSF.
#
# Usage: post-site-install.sh sitegroup env db-role domain

# Exit immediately on error and enable verbose log output.
set -ev

# Map the script inputs to convenient names:
# Acquia Hosting sitegroup (application) and environment.
sitegroup="$1"
env="$2"
# Database role. This is a truly unique identifier for an ACSF site and is e.g.
# part of the public files path.
db_role="$3"
# Internal (Acquia managed) domain name of the website. (No public domain name
# is assigned yet, immediately after installation.) The first part is a name
# that is unique per installed site. A small but significant difference with
# $db_role: if a site gets deleted and reinstalled with the same name, it gets
# a different $db_role.
internal_domain="$4"
# To get only the site name in ${name[0]}:
IFS='.' read -a name <<< $internal_domain

# BLT executable:
blt="/mnt/www/html/$sitegroup.$env/vendor/acquia/blt/bin/blt"

# Create and set Drush cache to unique local temporary storage per site.
# This approach isolates drush processes to completely avoid race conditions
# that persist after initial attempts at addressing in BLT: https://github.com/acquia/blt/pull/2922
cache_dir=`/usr/bin/env php /mnt/www/html/$sitegroup.$env/vendor/acquia/blt/scripts/blt/drush/cache.php $sitegroup $env $internal_domain`

# Execute the updates.
DRUSH_PATHS_CACHE_DIRECTORY="$cache_dir" $blt drupal:update --environment=$env --site=${name[0]} --define drush.uri=$internal_domain --verbose --no-interaction
result=$?

# Clean up the drush cache directory.
rm -rf "$cache_dir"

set +v

# Exit with the status of the BLT commmand. If the exit status is non-zero,
# Site Factory will send a notification of a partiolly failed install and will
# stop executing any further post-site-install hook scripts that would be in
# this directory (and get executed in alphabetical order).
exit $result
