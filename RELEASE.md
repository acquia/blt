This document outlines the process for creating a new BLT release.

To perform a release:

1. [Check build statuses](#check-build-statuses)
1. Update Canary
1. [Create a release](#create-a-release)

## Check build statuses

* [BLT 9.x](https://github.com/acquia/blt): [![Build Status](https://travis-ci.org/acquia/blt.svg?branch=9.x)](https://travis-ci.org/acquia/blt)
* [BLTed 9.x Travis](https://github.com/acquia-pso/blted8): [![Build Status](https://travis-ci.org/acquia-pso/blted8.svg?branch=9.x)](https://travis-ci.org/acquia-pso/blted8)
* [BLTed 9.x Pipelines](https://cloud.acquia.com/app/develop/applications/d74d1e87-f611-4e46-ba11-3e9b29cdcbdb/pipelines)
* [![Documentation Status](https://readthedocs.org/projects/blt/badge/?version=9.x)](http://blt.readthedocs.io/en/9.x/?badge=9.x)

## Update Canary

Submit a pull request to Canary with BLT updated to HEAD. Ensure tests pass.

## Create a release

### Prerequisites

* BLT's dependencies must be installed by running `composer install` in the BLT directory.
* Procure a [github personal access token](https://github.com/settings/tokens).
* Determine the version of your future release.

To both generate release notes and also create a new release on GitHub, execute:

    ./vendor/bin/robo release [tag] [token]
    
Add any manual steps or notable changes to the release notes. Click publish. Packagist is automatically updated.
