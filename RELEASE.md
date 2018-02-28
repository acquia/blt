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
* [![Documentation Status](https://readthedocs.org/projects/blt/badge/?version=8.x)](http://blt.readthedocs.io/en/9.x/?badge=8.x)

## Testing

### Prerequisites

In order to use these testing instructions:

* BLT's dependencies must be installed by running `composer install` in the BLT directory.
* You must install VirtualBox and Vagrant. See [Drupal VM](https://github.com/geerlingguy/drupal-vm#quick-start-guide) for more information.

### Execute tests

    ./vendor/bin/robo test

### Update Canary

Submit a pull request to Canary with BLT updated to HEAD. Ensure tests pass.

## Update CHANGELOG.md

### Prerequisites

* BLT's dependencies must be installed by running `composer install` in the BLT directory.
* Procure a [github api token](https://github.com/skywinder/github-changelog-generator#github-token).
* Determine the version of your future release.

### Execute command

Then, generate your release notes via:

    ./vendor/bin/robo release-notes [tag] [token]

## Create a release

To both generate release notes and also create a new release on GitHub, execute:

    ./vendor/bin/robo release [tag] [token]
