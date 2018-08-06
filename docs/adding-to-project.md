# Adding BLT to an existing project

Adding BLT to an existing project can be much more complex than simply creating a new site with BLT. There are a few reasons for this:

1. BLT expects your project to have a particular directory structure and set of files.
1. Your existing dependencies may conflict with BLT's dependencies.

Prerequisites:

1. The Drupal root must be in a top-level `docroot` directory.
1. You must **already use Composer to manage site dependencies**. If you don't, please see [Using Composer to manage Drupal site dependencies](https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies) and modify your project accordingly.
1. Ensure that your dependencies are all up to date via `composer update`. Assert that these updates do not break your project.

To add BLT to a pre-existing Drupal project, do the following:

1. `cd` into your existing project directory.
1. Set Composer's `minimum-stability` to `dev` and `prefer-stable` to `true`:

        composer config minimum-stability dev
        composer config prefer-stable true

1. Add BLT via composer:

        rm -rf vendor composer.lock
        composer require acquia/blt:^9.0.0 --no-update
        composer update

1. Continue following instructions after the `composer create-project` command in [Creating a new project with BLT](creating-new-project.md).
