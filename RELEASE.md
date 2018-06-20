This document outlines the process for creating a new BLT release.

To perform a release:

1. [Check build statuses](#check-build-statuses)
1. [Update Canary](##update-canary)
1. [Create a release](#create-a-release)
1. [Update the blt-project repo](#update-the-blt-project-repo)

## Check build statuses

* [BLT 9.x](https://github.com/acquia/blt): [![Build Status](https://travis-ci.org/acquia/blt.svg?branch=9.x)](https://travis-ci.org/acquia/blt)
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
* Determine the version of your future release, e.g., 9.1.0-alpha1.
* To both generate release notes and also create a new _draft_ release on GitHub, execute:

      ./vendor/bin/robo release [tag] [token]
    
* Add any manual steps or notable changes to the release notes. 
* Click publish. Packagist is automatically updated.

## Update the blt-project repo

In order for the `composer create-project acquia/blt-project my-project` command to pull the latest version of BLT, the [blt-project repo](https://github.com/acquia/blt-project) may need to be updated to point to the new release created in the BLT repo.  Once packagist shows the latest release of BLT as being available, test the composer create-project process. If it loads the old version, you likely need to update the blt-project repo

* Update the `composer.json` file in the blt-project repo to require the latest version of acquia/blt.
* Tag and release a new version of blt-project.
