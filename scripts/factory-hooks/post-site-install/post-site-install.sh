#!/bin/bash
#
# Factory Hook: post-site-install
#
# The existence of one or more executable files in the
# /factory-hooks/post-site-install directory will prompt them to be run after
# Drupal has completed installing.
# 
# This is necessary so that blt drupal:install tasks are invoked automatically
# when a site is created on ACSF. 
# 
# Usage: '/mnt/www/html/site.env/factory-hooks/post-site-install/post-site-install.sh' 'site' 'env' 'db-role' 'domain'
# Map the script inputs to convenient names.

# Exit immediately on error and enable verbose log output.
set -ev

# Acquia hosting site / environment names
site="$1"
env="$2"
# database role. (Not expected to be needed in most hook scripts.)
db_role="$3"
# The public domain name of the website. If the site uses a path based domain,
# the path is appended (without trailing slash), e.g. "domain.com/subpath".
domain="$4"

# BLT executable:
blt="/mnt/www/html/$site.$env/vendor/acquia/blt/bin/blt"

# You need the URI of the site factory website just created for drush to target that
# site. Without it, the drush command will fail. Use the uri.php file provided by the acsf module 
# to determine the correct URI based on the site, environment and db role arguments.
uri=`/usr/bin/env php /mnt/www/html/$site.$env/hooks/acquia/uri.php $site $env $db_role`

# Create array with site name fragments from ACSF uri.
IFS='.' read -a name <<< "${uri}"

# Create and set Drush cache to unique local temporary storage per site.
# This approach isolates drush processes to completely avoid race conditions
# that persist after initial attempts at addressing in BLT: https://github.com/acquia/blt/pull/2922

cacheDir=`/usr/bin/env php /mnt/www/html/$site.$env/vendor/acquia/blt/scripts/blt/drush/cache.php $site $env $uri`

# Print to cloud task log.
echo "Generated temporary drush cache directory: $cacheDir."

# Print to cloud task log.
echo "Running BLT post-install tasks on $uri domain in $env environment on the $site subscription."

# Run blt drupal:update tasks The trailing slash behind the domain works around a bug in
# Drush < 9.6 for path based domains: "domain.com/subpath/" is considered a
# valid URI but "domain.com/subpath" is not.
DRUSH_PATHS_CACHE_DIRECTORY=$cacheDir $blt drupal:update --environment=$env --site=${name[0]} --define drush.uri=$domain/ --verbose --no-interaction

# Clean up the drush cache directory.
echo "Removing temporary drush cache files."
rm -rf "$cacheDir"

set +v