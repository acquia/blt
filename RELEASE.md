This document outlines the process for creating a new BLT release.

# Testing

## Prerequisites

In order to use these testing instructions:

* The `blt` alias must be installed.
* Your LAMP stack must have host entry for `http://local.blted8.com` pointing to `./blted8/docroot`.
* MySQL must use `mysql://drupal:drupal@localhost/drupal:3306`. If this is not the case, modify the instructions below for your credentials.
* In order to test Drupal VM, you must install VirtualBox and Vagrant. See [Drupal VM](https://github.com/geerlingguy/drupal-vm#quick-start-guide) for more information.

## Create a new project via acquia/blt-project, uses local LAMP stack

This test verifies that a new project can be created using `acquia/blt-project` via composer. This also tests the `blt update` process.

    export COMPOSER_PROCESS_TIMEOUT=2000
    composer create-project acquia/blt-project blted8 --no-interaction
    cd blted8
    # Overwrite MySQL creds for your local machine, if necessary.
    # echo '$databases["default"]["default"]["username"] = "drupal";' >> docroot/sites/default/settings/local.settings.php
    # echo '$databases["default"]["default"]["password"] = "drupal";' >> docroot/sites/default/settings/local.settings.php
    blt local:setup
    cd docroot
    drush uli
    read -p "Press any key to continue"

    # This updates to the latest dev version.
    composer require acquia/blt:8.x-dev
    dr uli
    read -p "Press any key to continue"
    cd ../

## Creates a new project without acquia/blt-project "from scratch", uses Drupal VM

This test verifies that a new project can be created from scratch using blt, without blt-project. It also tests Drupal VM integration.

    rm -rf blted8
    mkdir blted8
    cd blted8
    git init
    composer init --stability=dev --no-interaction
    composer config prefer-stable true
    composer require acquia/blt:8.x-dev
    composer update
    blt vm
    blt local:setup
    drush @blted8.local uli
    drush @blted8.local ssh blt tests:behat
    read -p "Press any key to continue"
    vagrant destroy
    cd ../


## Updates existing project Pipelines project

    composer require acquia/blt:8.x-dev --no-update
    composer update
    git add -A
    git commit -m 'Updating acquia/blt to latest dev version.'
    git push origin
    pipelines start
    pipelines log

## Generate CHANGELOG.md

### Prerequisites

* Ruby 2.2.2+ must be installed. You may use [RVM](https://rvm.io/rvm/install) to use a directory specific version of Ruby. E.g., `rvm use 2.2.2`.
* [skywinder/github-changelog-generator](https://github.com/skywinder/github-changelog-generator) must be installed. E.g., `gem install github_changelog_generator`.
* Procure a [github api token](https://github.com/skywinder/github-changelog-generator#github-token).
* Determine the version of your future release.

Then, generate your release notes via:

    github_changelog_generator --token [token] --future-release=[version]

This will update CHANGELOG.md. The information for the new release should be copied and pasted into the GitHub release draft.

