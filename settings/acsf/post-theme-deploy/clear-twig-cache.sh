#!/bin/bash
# Clears TWIG cache for a site after a theme deploy. Executes via drush alias.
# @todo Remove this script after https://www.drupal.org/node/2752961 is 
# resolved and the corresponding Drupal core version is set as a minimum 
# requirement for BLT.
# $1 = The hosting site group.
# $2 = The hosting environment.
# $5 = The site domain.
site="$1"
env="$2"

# local drush  executable:
repo="/var/www/html/$site.$env"

cd $repo
drush @$1.$2 --uri=$5 ev '\Drupal\Core\PhpStorage\PhpStorageFactory::get("twig")->deleteAll();'
