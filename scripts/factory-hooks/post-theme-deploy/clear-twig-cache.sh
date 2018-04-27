#!/bin/bash
# Clears TWIG cache for a site after a theme deploy. Executes via drush alias.
# See https://docs.acquia.com/site-factory/theme/external#refresh.
# $1 = The hosting site group.
# $2 = The hosting environment.
# $5 = The site domain.
site="$1"
env="$2"

# local drush  executable:
repo="/var/www/html/$site.$env"

cd $repo
drush @$1.$2 --uri=$5 ev '\Drupal\Core\PhpStorage\PhpStorageFactory::get("twig")->deleteAll();'
