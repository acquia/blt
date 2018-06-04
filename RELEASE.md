This document outlines the process for creating a new BLT release.

To perform a release:

1. [Check build statuses](#check-build-statuses)
1. Update Canary
1. [Create a release](#create-a-release)

## Check build statuses

* [BLT 9.x](https://github.com/acquia/blt): [![Build Status](https://travis-ci.org/acquia/blt.svg?branch=9.x)](https://travis-ci.org/acquia/blt)
* [BLTed 9.x Travis](https://github.com/acquia-pso/blted8): [![Build Status](https://travis-ci.org/acquia-pso/blted8.svg?branch=9.x)](https://travis-ci.org/acquia-pso/blted8)
* [![Documentation Status](https://readthedocs.org/projects/blt/badge/?version=9.x)](http://blt.readthedocs.io/en/9.x/?badge=9.x)

## Update Canary

* Submit a pull request to Canary with BLT updated to HEAD.
    * Update BLT to the tip of HEAD. This is the same commit from which you will cut your tag.
    * Document update steps in `update.md`. Note any manual steps required and plan to add them to the BLT release notes (generated later).
* Ensure tests pass. Canary uses both Travis CI and Pipelines.
* Merge the pull request

## Create a release

### Prerequisites

* Pull the latest BLT version to your local machine `git pull`.
* BLT's dependencies must be installed by running `composer install` in the BLT directory.
* If you don't have one, procure a [github personal access token](https://github.com/settings/tokens). Optionall save in a password vault for future use.
* Determine the version of your future release. E.g., 9.1.0-alpha1.
* To both generate release notes and also create a new _draft_ release on GitHub, execute:

      ./vendor/bin/robo release [tag] [token]
    
* Add any manual steps or notable changes to the release notes. 
* Click publish. Packagist is automatically updated.
