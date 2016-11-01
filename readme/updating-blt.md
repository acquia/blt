# Updating BLT

## Updating a composer-managed version

If you are already using BLT via Composer, you can update to the latest version of BLT by running the following commands from your project's root directory:

      composer update acquia/blt --with-dependencies
      # Remove deprecated files.
      blt cleanup

Review and commit changes to your project files. For customized files like `.travis.yml` or `docroot/sites/default/settings.php` it is recommended that you use `git add -p` to select which specific line changes you'd like to stage and commit.

On occasion, updating BLT will result in changes to your project's composer.json file. This can happen when the upstream BLT project adds new dependencies or changes the pinned version of a dependency. In cases like this, BLT will print a message alerting you to run `composer update` an additional time so that you apply the changed dependencies locally.

Rarely, you may need to refresh your local environment via `blt local:setup` to provision new upstream changes.

### Modifying update behavior

By default BLT will modify your project's composer.json to conform with the [upstream composer.json template](https://github.com/acquia/blt/blob/8.x/template/composer.json). If you'd like to prevent a specific package or key in composer.json from being modified, use the `composer-exclude-merge` option:

      "blt": {
          "update": true,
          "composer-exclude-merge": {
              "require": [
                  "drupal/acsf",
                  "drupal/acquia_connector",
                  "drupal/memcache",
                  "drupal/search_api",
                  "drupal/search_api_solr"
              ],
              "require-dev": "*"
          }
      }

This would prevent the merging of any upstream updates to the composer.json configuration for a handful of modules in `require` and all packages in `require-dev`.

## Updating from a non-Composer-managed (very old) version

If you are using an older version of BLT that was not installed using Composer, you may update to the Composer-managed version by running the following commands:

1. Remove any dependencies that may conflict with upstream acquia/blt. You may add these back later after the upgrade, if necessary.

        composer remove drush/drush drupal/console phing/phing phpunit/phpunit squizlabs/php_codesniffer symfony/yaml drupal/coder symfony/console --no-interaction --no-update
        composer remove drush/drush drupal/console phing/phing phpunit/phpunit squizlabs/php_codesniffer symfony/yaml drupal/coder symfony/console --no-interaction --no-update
        composer config minimum-stability dev

1. (conditional) If you are using Lightning, verify that your version constraint allows it to be updated to the latest stable version:

        composer require drupal/lightning:~8 --no-update

1. Require acquia/blt version 8.3.0 as a dependency:

        composer require acquia/blt:8.3.0 --no-update

1. Update all dependencies:

        composer update

1. Execute update script:

        ./vendor/acquia/blt/scripts/blt/convert-to-composer.sh

1. Upgrade to the latest version of BLT:

        composer require acquia/blt:^8.3 --no-update
        composer update

Review and commit changes to your project files. For customized files like `.travis.yml` or `docroot/sites/default/settings.php` it is recommended that you use `git add -p` to select which specific line changes you'd like to stage and commit.
