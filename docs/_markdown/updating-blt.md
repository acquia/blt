# Updating BLT

## Updating a composer-managed version

If you are already using BLT via Composer, you can update to the latest version of BLT using composer.

1. To update to the latest version of BLT that is compatible with your existing dependencies, run the following commands:

        composer update acquia/blt --with-all-dependencies

   This will cause Composer to update all of your dependencies (in accordance with your version constraints) and permit the latest version of BLT to be installed.

1. Check the [release information](https://github.com/acquia/blt/releases) to see if there are special update instructions for the new version.
1. Review and commit changes to your project files.
1. Rarely, you may need to refresh your local environment via `blt setup`. This will drop your local database and re-install Drupal.
