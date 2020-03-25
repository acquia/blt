#!/bin/bash
#
# Factory Hook: db-update
#
# The existence of one or more executable files in the
# /factory-hooks/db-update directory will prompt them to be run *instead of* the
# regular database update (drush updatedb) command. So that update command will
# normally be part of the commands executed below.
#
# Usage: db-update.sh sitegroup env db-role domain custom-arg

# Exit immediately on error and enable verbose log output.
set -ev

# Map the script inputs to convenient names:
# Acquia Hosting sitegroup (application) and environment.
sitegroup="$1"
env="$2"
# Database role. This is a truly unique identifier for an ACSF site and is e.g.
# part of the public files path.
db_role="$3"
# The public domain name of the website. If the site uses a path based domain,
# the path is appended (without trailing slash), e.g. "domain.com/subpath".
domain="$4"

# BLT wants the name of the website, which we can derive from its internal
# domain name. Use the uri.php file provided by the acsf module to get the
# internal domain name based on the site, environment and db role arguments.
uri=`/usr/bin/env php /mnt/www/html/$sitegroup.$env/hooks/acquia/uri.php $sitegroup $env $db_role`
# To get only the site name in ${name[0]}:
IFS='.' read -a name <<< "${uri}"

# BLT executable:
blt="/mnt/www/html/$sitegroup.$env/vendor/acquia/blt/bin/blt"

# Create and set Drush cache to unique local temporary storage per site.
# This approach isolates drush processes to completely avoid race conditions
# that persist after initial attempts at addressing in BLT: https://github.com/acquia/blt/pull/2922
cache_dir=`/usr/bin/env php /mnt/www/html/$sitegroup.$env/vendor/acquia/blt/scripts/blt/drush/cache.php $sitegroup $env $uri`

echo "Generated temporary drush cache directory: $cache_dir."

echo "Running BLT deploy tasks on $uri domain in $env environment on the $sitegroup subscription."

# Run blt drupal:update tasks. The trailing slash behind the domain works
# around a bug in Drush < 9.6 for path based domains: "domain.com/subpath/" is
# considered a valid URI but "domain.com/subpath" is not.
DRUSH_PATHS_CACHE_DIRECTORY="$cache_dir" $blt drupal:update --environment=$env --site=${name[0]} --define drush.uri=$domain/ --verbose --no-interaction

# Clean up the drush cache directory.
echo "Removing temporary drush cache files."
rm -rf "$cache_dir"

set +v

# @todo Exit with the status of the BLT commmand. If the exit status is non-zero,
# Site Factory will send a notification of a failed 'blt drupal:update',
# interrupting the execution of additional db-update scripts.
