# Updating BLT

## Updating a composer-managed version

If you are already using BLT via Composer, you can update to the latest version of BLT using composer.

1. Run the following commands:

        # update blt and its dependencies
        composer update acquia/blt --with-dependencies
  
   Rarely, the first command will fail with a version conflict. If this happens, run `composer update` so that Composer can try to resolve a new set of interoperable dependencies.

1. Check the [release information](https://github.com/acquia/blt/releases) to see if there are special update instructions for the new version. 
1. Review and commit changes to your project files.
1. Rarely, you may need to refresh your local environment via `blt local:setup`. This will drop your local database and re-install Drupal.

### Modifying update behavior

By default BLT will modify a handful of files in your project to conform to the [upstream template](https://github.com/acquia/blt/blob/8.x/template). If you'd like to prevent this, set `extra.blt.update` to `false` in `composer.json`:

      "extra": {
        "blt": {
            "update": false
        }
      }

Please not that if you choose to do this, it is your responsibility to track upstream changes. This is very likely to cause issues when you upgrade BLT to a new version.

### Modifying BLT's default Composer values

BLT merges default values for composer.json using [wikimedia/composer-merge-plugin](https://github.com/wikimedia/composer-merge-plugin):

        "merge-plugin": {
            "require": [
                "vendor/acquia/blt/composer.required.json",
                "vendor/acquia/blt/composer.suggested.json"
            ],
            "merge-extra": true,
            "merge-extra-deep": true,
            "merge-scripts": true
        },

This merges `require`, `require-dev`, `autoload`, `autoload-dev`, `scripts`, and `extra` from BLT's own vendored files. These the merged includes are split into two groups: required and suggested. You may remove the suggested packages by deleting the `vendor/acquia/blt/composer.suggested.json` line from your composer.json.

You may also override the value for any key by defining a different value for the same key in your root composer.json.

## Updating from a non-Composer-managed (very old) version

If you are using an older version of BLT that was not installed using Composer, you may update to the Composer-managed version by running the following commands:

1. Remove any dependencies that may conflict with upstream acquia/blt. You may add these back later after the upgrade, if necessary.

        composer remove drush/drush drupal/console phing/phing phpunit/phpunit squizlabs/php_codesniffer symfony/yaml drupal/coder symfony/console --no-interaction --no-update
        composer remove drush/drush drupal/console phing/phing phpunit/phpunit squizlabs/php_codesniffer symfony/yaml drupal/coder symfony/console --no-interaction --no-update --dev
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
