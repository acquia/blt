#!/bin/bash
# Clears TWIG cache for a site after a theme deploy. Executes via drush alias.
# $1 = The hosting site group.
# $2 = The hosting environment.
# $5 = The site domain.
# Example:
#   drush8 @conagra.01live --uri=readysteat.conagra.acsitefactory.com ...
drush8 @$1.$2 --uri=$5 ev '\Drupal\Core\PhpStorage\PhpStorageFactory::get("twig")->deleteAll();'