This document outlines the process for creating a new BLT release.

# Testing

## Prerequisites

In order to use these testing instructions:

* The `blt` alias must be installed.
* Your LAMP stack must have host entry for `http://local.blted8.com` pointing to `./blted8/docroot`.
* MySQL must use `mysql://drupal:drupal@localhost/drupal:3306`. If this is not the case, modify the instructions below for your credentials.
* In order to test Drupal VM, you must install VirtualBox and Vagrant. See [Drupal VM](https://github.com/geerlingguy/drupal-vm#quick-start-guide) for more information.

## Execute tests

    ./scripts/blt/test-blt.sh [tag]

## Create release

    ./scripts/blt/release-blt.sh [tag] [token]
 
## Generate CHANGELOG.md

### Prerequisites

* Ruby 2.2.2+ must be installed. You may use [RVM](https://rvm.io/rvm/install) to use a directory specific version of Ruby. E.g., `rvm use 2.2.2`.
* [skywinder/github-changelog-generator](https://github.com/skywinder/github-changelog-generator) must be installed. E.g., `gem install github_changelog_generator`.
* Procure a [github api token](https://github.com/skywinder/github-changelog-generator#github-token).
* Determine the version of your future release.

Then, generate your release notes via:

    github_changelog_generator --token [token] --future-release=[version]

This will update CHANGELOG.md. The information for the new release should be copied and pasted into the GitHub release draft.

