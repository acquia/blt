This document outlines the process for creating a new BLT release.

To perform a release:

1. [Check build statuses](#check-build-statuses)
1. [Review issue labels](#review-issue-labels)
1. [Test via Canary](#test-via-canary)
1. [Create a release](#create-a-release)
1. [Update the blt-project repo](#update-the-blt-project-repo)

For major releases, coordinate with ORCA prior to starting this process to ensure global test suites don't break. Afterwards, update the README and default branches in acquia/blt as well as acquia/blt-project to use the new major release.

## Check build statuses

* [BLT 12.x](https://github.com/acquia/blt):
[![Build Status](https://travis-ci.com/acquia/blt.svg?branch=12.x)](https://travis-ci.com/acquia/blt)
* [BLT 11.x](https://github.com/acquia/blt):
[![Build Status](https://travis-ci.com/acquia/blt.svg?branch=11.x)](https://travis-ci.com/acquia/blt)

## Review issue labels

Review the issues and pull requests that will make up the release and ensure they are labelled correctly so that the changelog and release notes will be generated with the correct categories (new features, bug fixes, etc...).

## Test via Canary

* Submit a pull request to Canary with BLT updated to HEAD.
    * Update BLT to the tip of HEAD. This is the same commit from which you will cut your tag.
    * Note any manual steps required and plan to add them to the BLT release notes (generated later).
* Ensure tests pass, and smoke test the site in Cloud environments.

## Create a release

* Pull the latest BLT version to your local machine `git pull`.
* BLT's dependencies must be installed by running `composer install` in the BLT directory.
* If you don't have one, procure a [github personal access token](https://github.com/settings/tokens), and optionally save in a password vault for future use.
* Determine the version of your future release, e.g., 9.1.0-alpha1.
* Ensure that the remote name for the BLT repository is `upstream`.
* To both generate release notes and also create a new _draft_ release on GitHub, execute:

      ./vendor/bin/robo release [tag] [token]

* Add any manual steps or notable changes to the release notes.
* Click publish. Packagist is automatically updated.
* Add fix version and close tickets in JIRA.

## Finish updating Canary

* Update PR to use new stable release
* Merge PR

## Publicize the release

Let folks in the #blt Drupal Slack channel know about exciting features or important changes in the new release and link to the release notes.
