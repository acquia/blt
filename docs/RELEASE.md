This document outlines the process for creating a new BLT release.

To perform a release:

1. [Check build statuses](#check-build-statuses)
1. [Test via Canary](#test-via-canary)
1. [Create a release](#create-a-release)
1. [Update the blt-project repo](#update-the-blt-project-repo)

## Check build statuses

* [BLT 10.x](https://github.com/acquia/blt):
[![Build Status](https://travis-ci.com/acquia/blt.svg?branch=10.x)](https://travis-ci.com/acquia/blt)
[![Documentation Status](https://readthedocs.org/projects/blt/badge/?version=10.x)](http://blt.readthedocs.io/en/10.x/?badge=10.x)
* [BLT 9.2.x](https://github.com/acquia/blt):
[![Build Status](https://travis-ci.com/acquia/blt.svg?branch=9.2.x)](https://travis-ci.com/acquia/blt)
[![Documentation Status](https://readthedocs.org/projects/blt/badge/?version=9.2.x)](http://blt.readthedocs.io/en/9.2.x/?badge=9.2.x)

## Test via Canary

* Submit a pull request to Canary with BLT updated to HEAD.
    * Update BLT to the tip of HEAD. This is the same commit from which you will cut your tag.
    * Document update steps in `update.md`. Note any manual steps required and plan to add them to the BLT release notes (generated later).
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

## Finish updating Canary

* Update PR to use new stable release
* Merge PR

## Update the blt-project repo

If there have been any changes to the blt-project subtree since you last created a release, you should push these to blt-project and create a new release now.

The mainline branches of blt-project (e.g. 10.x) should always install a development version of BLT (e.g. 10.x-dev), while stable releases of blt-project (e.g. 10.0.0) should always install stable versions of BLT (e.g. 10.0.0).

In order to accomplish this, the composer.json in the blt-project subtree split should depend on a development version of BLT by default. When you create a new release of blt-project, you'll need to temporarily override this to a stable dependency by following these steps.

* Modify `subtree-splits/blt-project/composer.json` to depend on the latest stable release of BLT and commit and push this change to BLT.
* Push these changes to blt-project (this will become your stable release): `./vendor/bin/robo subtree:push:blt-project`
* Create a stable release for blt-project on Github using this latest release as a tag.
* Revert the previous commit so that blt-project once again requires a development version of BLT, and push to BLT.
* Push these changes to blt-project a final time: `./vendor/bin/robo subtree:push:blt-project`.

Obviously this is a clunky process, but it produces the best result for end users and fortunately shouldn't need to happen often. It could probably be automated by incorporating the above steps into a Robo command, and/or setting up a Github service to automatically push subtree changes to the blt-project split.

## Update the blt-require-dev repo

In order to update the `require-dev` dependencies for BLT based projects, you must:

* Update the `composer.json` file in the `subtree-splits/blt-require-dev` directory's `composer.json`.
* Commit the changes.
* Execute `./vendor/bin/robo subtree:push:blt-require-dev`
* Tag and release a new version of blt-require-dev.

## For major releases only

Make sure to update the README and default branches in acquia/blt as well as acquia/blt-project to use the new major release.
