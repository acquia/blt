# BLT installation and updates

## System Requirements

You should be able to use the following tools on the command line of your native operating system:

* [Git](https://git-scm.com/)
* [Composer](https://getcomposer.org/download/)
* PHP 5.6+, PHP installation instructions:
    * [OSX](http://justinhileman.info/article/reinstalling-php-on-mac-os-x/)
    * [Windows](http://php.net/manual/en/install.windows.php)
    * [Linux](http://php.net/manual/en/install.unix.debian.php)
* [NPM](https://nodejs.org/en/download/) (for [Lightning](https://github.com/acquia/lightning) support)

### Recommended tools and configuration

* Globally install pretissimo for parallelized composer downloads:

        composer global require "hirak/prestissimo:^0.3"

* If you have xDebug enabled for your PHP CLI binary, it is highly recommended that you disable it to dramatically improve performance.

## Creating a new project with BLT

1. Create a new project using the [blt-project](https://github.com/acquia/blt-project) template:

        composer create-project acquia/blt-project:8.x-dev MY_PROJECT --no-interaction
        cd MY_PROJECT

1. Install the `blt` alias and follow on-screen instructions:

        ./vendor/bin/blt install-alias

1. Customize BLT configuration files:
    * `project.yml`
    * `docroot/sites/default/settings/local.settings.php`
        * Add your local DB credentials to `$databases`
1. Replace tokens in new BLT-generated files with your custom values in project.yml:

        blt configure

1. Follow instructions for [Setting up your \*AMP stack](#set-up-your-amp-stack)
1. Follow instructions for <a href="#build-your-projects-local-dependencies-and-install-drupal-locally">installing Drupal locally</a>. Don't install Drupal locally using your web browser.
1. (optional) Modify project files. Important files that you may want to modify include:
    * composer.json. Note that Drupal core, contrib, and third party dependencies are all managed here.
    * Project’s root README.md.
    * Other project documentation in the readme directory.

## Adding BLT to an existing project

To add BLT to a pre-existing Drupal project, do the following:

1. Ensure that your project directory structure is Acquia-cloud compatible by asserting that the Drupal root is in a top-level folder called `docroot`.
1. If you currently manage your dependencies via Composer, ensure that they are all up to date via `composer update`. Assert that these updates do not break your project.
1. `cd` into your existing project directory.
1. Add BLT via composer and initialize it:

        composer require acquia/blt:~8 --dev
        ./vendor/bin/blt init

1. Follow instructions for [Setting up your \*AMP stack](#set-up-your-42amp-stack)
1. Follow instructions for <a href="#build-your-projects-local-dependencies-and-install-drupal-locally">installing Drupal locally</a>. Don't install Drupal locally using your web browser.
1. (optional) Modify project files. Important files that you may want to modify include:
    * composer.json. Note that Drupal core, contrib, and third party dependencies are all managed here.
    * Project’s root README.md.
    * Other project documentation in the readme directory.

## Updating BLT

### Updating a composer-managed version

If you are already using BLT via Composer, you can update to the latest version of BLT by running the following commands from your project's root directory:

      composer update acquia/blt
      blt update

Review and commit changes to your project files. For customized files like `.travis.yml` or `docroot/sites/default/settings.php` it is recommended that you use `git add -p` to select which specific line changes you'd like to stage and commit.

Rarely, you may need to refresh your local environment via `blt local:setup` to provision new upstream changes.

### Updating from a non-Composer-managed version

If you are using an older version of BLT that was not installed using Composer, you may update to the Composer-managed version by running the following commands:

1. Remove any dependencies that may conflict with upstream acquia/blt. You may add these back later after the upgrade, if necessary.

        composer remove drush/drush drupal/console phing/phing phpunit/phpunit squizlabs/php_codesniffer symfony/yaml drupal/coder symfony/console --no-interaction --no-update
        composer remove drush/drush drupal/console phing/phing phpunit/phpunit squizlabs/php_codesniffer symfony/yaml drupal/coder symfony/console --dev --no-interaction --no-update
        composer config minimum-stability dev

1. (conditional) If you are using Lightning, verify that your version constraint allows it to be updated to the latest stable version:

        composer require drupal/lightning:~8 --no-update

1. Require acquia/blt as a dev dependency:

        composer require acquia/blt:~8 --dev --no-update

1. Update all dependencies:

        composer update

1. Execute update script:

        ./vendor/acquia/blt/scripts/blt/convert-to-composer.sh

Review and commit changes to your project files. For customized files like `.travis.yml` or `docroot/sites/default/settings.php` it is recommended that you use `git add -p` to select which specific line changes you'd like to stage and commit.

## Set up your \*AMP stack

Before building your project dependencies and installing Drupal, you must have a fully functional \*AMP stack on your local machine. BLT intentionally does not provide this local development environment--that is outside of the scope of BLT’s intended responsibilities. It does, however, make recommendations for which tools you should use to manage your stack.

Please see [Local Development](readme/local-development.md) for more information on setting up your \*AMP stack:

* [Acquia Dev Desktop](readme/local-development.md#using-acquia-dev-desktop-for-blt-generated-projects)
* [Drupal VM](readme/local-development.md#using-drupal-vm-for-blt-generated-projects)
* [Other](readme/local-development.md#alternative-local-development-environments)

When you have completed setting up your local \*AMP stack, double check that the following pieces of information are still correct:

* Local site DB credentials: `$databases` in docroot/sites/default/settings/local.settings.php
* Local site URL: `$options[‘uri’]` in docroot/sites/default/local.drushrc.php

## Build your project’s local dependencies and install Drupal locally

Run the following command from the project root: `blt local:setup`. This will do a lot of things for you, including:

* Building dependencies
* Installing local git hooks
* Generating local.yml for Behat
* Installing Drupal locally

When this task is complete, you should have a fully functioning Drupal site on your local machine. You can login to the site by running `drush uli`.

Note that all common project tasks are executed through `blt`. For a full list of available tasks, run `blt -l`.

## Next Steps

Now that your new project works locally, read through the new [README.md](https://github.com/acquia/blt/blob/8.x/template/README.md) file in your project to learn how to perform common project tasks and integrate with third party tools.
