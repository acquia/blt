This document outlines the process for creating a new BLT release.

To perform a release:

1. [Check build statuses](#check-build-statuses)
1. [Run tests](#testing) locally.
1. [Generate and commit updated CHANGELOG.md](#update-changelogmd).
1. [Create a release](#create-a-release)

## Check build statuses

* [BLT 8.x](https://github.com/acquia/blt): [![Build Status](https://travis-ci.org/acquia/blt.svg?branch=8.x)](https://travis-ci.org/acquia/blt) 
* [BLTed 8.x Travis](https://github.com/acquia-pso/blted8): [![Build Status](https://travis-ci.org/acquia-pso/blted8.svg?branch=8.x)](https://travis-ci.org/acquia-pso/blted8)
* [BLTed 8.x Pipelines](https://cloud.acquia.com/app/develop/applications/d74d1e87-f611-4e46-ba11-3e9b29cdcbdb/pipelines)
* [![Documentation Status](https://readthedocs.org/projects/blt/badge/?version=8.x)](http://blt.readthedocs.io/en/8.x/?badge=8.x) 

## Testing

### Prerequisites

In order to use these testing instructions:

* The `blt` alias must be installed.
* Your LAMP stack must have host entry for `http://local.blted8.com` pointing to `./blted8/docroot`.
* MySQL must use `mysql://drupal:drupal@localhost/drupal:3306`.
* In order to test Drupal VM, you must install VirtualBox and Vagrant. See [Drupal VM](https://github.com/geerlingguy/drupal-vm#quick-start-guide) for more information.

### Execute tests

    ./scripts/blt/pre-release-tests.sh [tag]
 
## Update CHANGELOG.md

### Prerequisites

* BLT's dependencies must be installed by running `composer install` in the BLT directory.
* Ruby 2.2.2+ must be installed. You may use [RVM](https://rvm.io/rvm/install) to use a directory specific version of Ruby. E.g., `rvm use 2.2.2`.
* [skywinder/github-changelog-generator](https://github.com/skywinder/github-changelog-generator) must be installed. E.g., `gem install github_changelog_generator`.
* Procure a [github api token](https://github.com/skywinder/github-changelog-generator#github-token).
* Determine the version of your future release.

### Execute command

Then, generate your release notes via:

    ./vendor/bin/robo release-notes [tag] [token]


This will update CHANGELOG.md and create a commit locally.

## Create a release

To both generate release notes and also create a new release on GitHub, execute:

    ./vendor/bin/robo release --update-changelog [tag] [token]

This is a potentially destructive command. It will:
 
 * Perform a hard reset on the 8.x and 8.x-release branches of your local repository
 * Update CHANGELOG.md, commit, and __push upstream__
 * Merge 8.x into 8.x-release and __push upstream__
 * Create a draft release on GitHub, populated with release notes
