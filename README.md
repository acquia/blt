# BLT

[![Build Status](https://travis-ci.org/acquia/blt.svg?branch=8.x)](https://travis-ci.org/acquia/blt) [![Documentation Status](https://readthedocs.org/projects/blt/badge/?version=8.x)](http://blt.readthedocs.io/en/8.x/?badge=8.x) [![Packagist](https://img.shields.io/packagist/v/acquia/blt.svg)](https://packagist.org/packages/acquia/blt) [![Stories in Ready](https://badge.waffle.io/acquia/blt.png?label=ready&title=Ready)](http://waffle.io/acquia/blt)

BLT (Build and Launch Tool) provides an automation layer for testing, building, and launching Drupal 8 applications.

You can find all BLT documentation on [Read the Docs](http://blt.readthedocs.io):

* [Latest (stable) release documentation](http://blt.readthedocs.io/en/stable/)
* [Latest documentation (8.x / 8.x-dev)](http://blt.readthedocs.io/en/latest/)
* [8.8.x documentation](http://blt.readthedocs.io/en/8.8.x/)

## Getting started

See [INSTALL.md](INSTALL.md) for a list of prequisites and links to instructions for [creating new projects](https://github.com/acquia/blt/blob/8.x/readme/creating-new-project.md), [adding BLT to existing projects](https://github.com/acquia/blt/blob/8.x/readme/adding-to-project.md), and [updating BLT](https://github.com/acquia/blt/blob/8.x/readme/updating-blt.md).

## Videos

* [BLT Project Creation](https://www.youtube.com/watch?v=KBwS0fsmXRs)
* [Deploying to Acquia Cloud](https://www.youtube.com/watch?v=jjnPMvZ2x-c)

## Releases and versioning

BLT does not use Semver. Instead, it uses `[drupal-major-version]-[blt-major-version]-[blt-minor-version]`. E.g., 8.8.1.

Typically, the last two most major versions of BLT are supported actively. The newest major version will recieve both bug fixes and new features, while the penultimate major version will recieve bug fixes for at least two months. For example, if 8.8.1 is the most recent version of BLT:

* As of the first cut tag for 8.8.x, 8.7.x will enter "LTS".
    * 8.7.x will continue to receive bug fixes and minor features
    * 8.7.x will not receive any major new features or backwards incompatible changes
* Two months after the first cut tag for 8.8.x
    * 8.7.x will no longer be supported
    * 8.9.x will be created for major new features.
* At some point later, 8.9.0 will be cut, and 8.8.x will enter "LTS".

### Release support status

| Major Version | Status                      |
|---------------|-----------------------------|
| 8.9.x         | Supported                   |
| 8.8.x         | Supported, LTS thru 9/12/17 |
| 8.7.x         | Unsupported, EOL            |

## Philosophy and Purpose

BLT is designed to improve efficiency and collaboration across Drupal projects by providing a common set of tools and standardized structure. It was born out of the need to reduce re-work, project set up time, and developer onboarding time.

Its explicit goals are to:

* Provide a standard project template for Drupal based projects
* Provide tools that automate the setup, testing, launching, and maintenance work for projects
* Document and enforce Drupal standards and best practices via default configuration, automated testing, and continuous integration

Its scope is discretely defined. It is *not* intended to provide:

* Drupal application features (e.g., workflow, media, layout, pre-fabbed content types, etc.)
* A local hosting environment
* A replacement for good judgement (as with Drupal, it leaves you the freedom to make mistakes)

## Features

* [Local Git Hooks](scripts/git-hooks)
    * pre-commit: Checks for Drupal coding standards compliance
    * commit-msg: Check for proper formatting and syntax
* [Testing Framework](template/tests).
    * Behat: default `local.yml` configuration, example tests, `FeatureContext.php`
    * PHPUnit: default tests for ensuring proper functioning of BLT provided components
* [Commands to automate project tasks](readme/project-tasks.md), like:
    * Test execution
    * Frontend asset compilation
    * Syncing environments
* [Deployment Artifact Generation](readme/deploy.md)
    * Building production-only dependencies
    * Sanitation of production code
* [Continuous Integration & Deployment](readme/ci.md)
    * [Acquia Pipelines](https://dev.acquia.com/request-invite-acquia-pipelines)
    * [Travis CI](https://travis-ci.com)
    * [GitHub](https://github.com)

# Support and contribution

BLT is provided as an open source tool in the hope that it will enable developers to easily generate new Drupal projects that conform to Acquia Professional Services' best practices.

Please feel free to contribute to the project or file issues via the GitHub issue queue. See [CONTRIBUTING.md](CONTRIBUTING.md) for contribution guidelines and instructions.

# License

Copyright (C) 2016 Acquia, Inc.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
