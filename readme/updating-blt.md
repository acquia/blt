# Updating BLT

## Updating a composer-managed version

If you are already using BLT via Composer, you can update to the latest version of BLT using composer.

1. To update to the latest version of BLT that is compatible with your existing dependencies, run the following commands:

        composer update acquia/blt --with-all-dependencies

   This will cause Composer to update all of your dependencies (in accordance with your version constraints) and permit the latest version of BLT to be installed.

1. Check the [release information](https://github.com/acquia/blt/releases) to see if there are special update instructions for the new version.
1. Review and commit changes to your project files.
1. Rarely, you may need to refresh your local environment via `blt setup`. This will drop your local database and re-install Drupal.

### Modifying update behavior

By default BLT will modify a handful of files in your project to conform to the [upstream template](https://github.com/acquia/blt/blob/9.x/template). If you'd like to prevent this, set `extra.blt.update` to `false` in `composer.json`:

      "extra": {
        "blt": {
            "update": false
        }
      }

Please note that if you choose to do this, it is your responsibility to track upstream changes. This is very likely to cause issues when you upgrade BLT to a new version.

## Updating from a non-Composer-managed (very old) version

If you are using an older version of BLT that was not installed using Composer, you may update to the Composer-managed version by running the following commands:

1. Remove any dependencies that may conflict with upstream acquia/blt. You may add these back later after the upgrade, if necessary.

        composer remove drush/drush drupal/console phing/phing phpunit/phpunit squizlabs/php_codesniffer symfony/yaml drupal/coder symfony/console --no-interaction --no-update
        composer remove drush/drush drupal/console phing/phing phpunit/phpunit squizlabs/php_codesniffer symfony/yaml drupal/coder symfony/console --no-interaction --no-update --dev
        composer config minimum-stability dev

1. (conditional) If you are using Lightning, verify that your version constraint allows it to be updated to the latest stable version:

        composer require drupal/lightning:~8 --no-update

1. Require acquia/blt version 9.0.5 as a dependency:

        composer require acquia/blt:9.0.5 --no-update

1. Update all dependencies:

        composer update

1. Execute update script:

        ./vendor/acquia/blt/scripts/blt/convert-to-composer.sh

1. Upgrade to the latest version of BLT:

        composer require acquia/blt:^9.0.5 --no-update
        composer update

1. If using Travis CI, re-initialize .travis.yml and re-apply customizations:

        rm .travis.yml && blt recipes:ci:travis:init

1. Cleanup deprecated files

        rm -rf .git/hooks && mkdir .git/hooks
        blt blt:source:cleanup

1. If using Drupal VM, re-create VM:

        blt recipes:drupalvm:destroy
        blt vm

Review and commit changes to your project files. For customized files like `.travis.yml` or `docroot/sites/default/settings.php`, it is recommended that you use `git add -p` to select which specific line changes you'd like to stage and commit.
