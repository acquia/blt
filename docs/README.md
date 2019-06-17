# BLT

![BLT logo of stylized sandwich](https://github.com/acquia/blt/raw/10.x/docs/blt-logo.png)

[![Build Status](https://travis-ci.com/acquia/blt.svg?branch=10.x)](https://travis-ci.com/acquia/blt) [![Documentation Status](https://readthedocs.org/projects/blt/badge/?version=10.x)](http://blt.readthedocs.io/en/10.x/?badge=10.x) [![Packagist](https://img.shields.io/packagist/v/acquia/blt.svg)](https://packagist.org/packages/acquia/blt) [![Stories in Ready](https://badge.waffle.io/acquia/blt.png?label=ready&title=Ready)](http://waffle.io/acquia/blt)

BLT (Build and Launch Tool) provides an automation layer for testing, building, and launching Drupal 8 applications.

You can find all BLT documentation on [Read the Docs](http://blt.readthedocs.io):

* [Latest release documentation (10.x)](http://blt.readthedocs.io/en/latest/) (best for guidance on general issues and best practices)
* [Previous release documentation (9.2.x)](http://blt.readthedocs.io/en/stable/) (best for features specific to 9.2.x)

## Getting started

See [INSTALL.md](INSTALL.md) for a list of prequisites and links to instructions for [creating new projects](creating-new-project.md), [adding BLT to existing projects](adding-to-project.md), and [updating BLT](updating-blt.md).

## Videos

* [BLT Project Creation](https://www.youtube.com/watch?v=KBwS0fsmXRs)
* [Deploying to Acquia Cloud](https://www.youtube.com/watch?v=jjnPMvZ2x-c)

## Releases and versioning

BLT generally has two supported releases at any given time, each corresponding to a major [semantic version](https://semver.org/). The newest supported major version will receive bug fixes and new features, while the penultimate major version will receive only critical bug and security fixes for six months. Major and minor releases with new features and bug fixes occur on the first Wednesday of every month. Security and bug-fix releases occur only when necessary on the third Wednesday of every month.

This is intended to coincide with the [Drupal core release cycle](https://www.drupal.org/core/release-cycle-overview), so that users can continue to use a single BLT major release through the lifecycle of a Drupal core minor release.

### Release support status

| BLT version | Support status        | End of life    |  Drupal versions* | Drush versions |
|-------------|-----------------------|----------------|-------------------|----------------|
| **10.x**    | **Supported, stable** | **>=May 2020** | **8.6, 8.7**      | **>=9.5.0**    |
| 9.2.x       | Bug fixes only        | Dec 2019       | 8.6, 8.7          | >=9.4.0        |
| <=9.x       | Unsupported           | May 2019       | 8.5               | >=9.1.0        |

*Note that when a particular release of any package (such as Drupal or PHP) stops being supported by its maintainer, BLT may cease supporting that release as well. For instance, as of December 2019, BLT 10.x will no longer support Drupal 8.6, and will instead support Drupal 8.7 and 8.8  in accordance with [Drupal security policy](https://www.drupal.org/drupal-security-team/general-information).

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

* [Local Git Hooks](https://github.com/acquia/blt/tree/9.x/scripts/git-hooks)
    * pre-commit: Checks for Drupal coding standards compliance
    * commit-msg: Check for proper formatting and syntax
* [Testing Framework](https://github.com/acquia/blt/tree/9.x/template/tests).
    * Behat: default `local.yml` configuration, example tests, `FeatureContext.php`
    * PHPUnit: default tests for ensuring proper functioning of BLT provided components
* [Commands to automate project tasks](project-tasks.md), like:
    * Test execution
    * Frontend asset compilation
    * Syncing environments
* [Deployment Artifact Generation](deploy.md)
    * Building production-only dependencies
    * Sanitation of production code
* [Continuous Integration & Deployment](ci.md)
    * [Acquia Pipelines](https://dev.acquia.com/request-invite-acquia-pipelines)
    * [Travis CI](https://travis-ci.com)
    * [GitHub](https://github.com)

# Support and contribution

BLT is provided as an open source tool in the hope that it will enable developers to easily generate new Drupal projects that conform to Acquia Professional Services' best practices.

Please feel free to contribute to the project or file issues via the GitHub issue queue. See [CONTRIBUTING.md](CONTRIBUTING.md) for contribution guidelines and instructions.

We also provide a limited [FAQ](FAQ.md) for common issues.

# License

Copyright (C) 2016 Acquia, Inc.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
