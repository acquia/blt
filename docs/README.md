# BLT

![BLT logo of stylized sandwich](https://github.com/acquia/blt/raw/10.0.x/blt-logo.png)

[![Build Status](https://travis-ci.org/acquia/blt.svg?branch=10.0.x)](https://travis-ci.org/acquia/blt) [![Documentation Status](https://readthedocs.org/projects/blt/badge/?version=10.0.x)](http://blt.readthedocs.io/en/10.0.x/?badge=10.0.x) [![Packagist](https://img.shields.io/packagist/v/acquia/blt.svg)](https://packagist.org/packages/acquia/blt) [![Stories in Ready](https://badge.waffle.io/acquia/blt.png?label=ready&title=Ready)](http://waffle.io/acquia/blt)

BLT (Build and Launch Tool) provides an automation layer for testing, building, and launching Drupal 8 applications.

You can find all BLT documentation on [Read the Docs](http://blt.readthedocs.io):

* [Latest release documentation (10.0.x)](http://blt.readthedocs.io/en/latest/) (best for guidance on general issues and best practices)
* [LTS release documentation (9.2.x)](http://blt.readthedocs.io/en/stable/) (best for features specific to 9.2.x)

## Getting started

See [INSTALL.md](INSTALL.md) for a list of prequisites and links to instructions for [creating new projects](creating-new-project.md), [adding BLT to existing projects](adding-to-project.md), and [updating BLT](updating-blt.md).

## Videos

* [BLT Project Creation](https://www.youtube.com/watch?v=KBwS0fsmXRs)
* [Deploying to Acquia Cloud](https://www.youtube.com/watch?v=jjnPMvZ2x-c)

## Releases and versioning

Up to the last two most major versions of BLT are actively supported. The newest major version will receive both bug fixes and new features, while the penultimate major version will recieve bug fixes for at least two months. For example, if 8.8.1 is the most recent version of BLT:

* As of the first cut tag for 8.8.x, 8.7.x will enter "LTS".
    * 8.7.x will continue to receive bug fixes and minor features
    * 8.7.x will not receive any major new features or backwards incompatible changes
* Two months after the first cut tag for 8.8.x
    * 8.7.x will no longer be supported
    * 8.9.x will be created for major new features.
* At some point later, 8.9.0 will be cut, and 8.8.x will enter "LTS".

### Release support status

| Major Version | Support Status              | Drupal | Drush          | Dev Status   |
|---------------|-----------------------------|--------|----------------|--------------|
| 10.0.x        | Unsupported (pre-alpha)     | >=8.6  | >=9.4.0        | \*active dev |
| 9.2.x         | Supported                   | 8.6    | >=9.4.0        | \*active dev |
| 9.x           | LTS, EOL TBA                | 8.5    | >=9.1.0        | \*bug fixes  |
| 8.9.x         | Unsupported, EOL            | <=8.5  | ~8             |              |
| 8.8.x         | Unsupported, EOL            | <=8.3  | ~8             |              |
| 8.7.x         | Unsupported, EOL            | <=8.3  | ~8             |              |

### 10.0.x branch

The 10.0.x branch is in active development and is not currently stable or supported. It requires Drush 9.4.x, Drupal 8.6.x, and PHP 7+. It is considered a major release because it removes the Composer merge plugin that BLT previously used to manage dependencies.

### 9.2.x branch

The 9.2.x branch is currently in development and supported. It requires Drush 9.4.x and Drupal 8.6.x.

### 9.x branch

The 9.x branch is currently in LTS and receiving bug fixes only. It requires Drush 9 and Drupal 8.5.x. It will be supported until Drupal 8.5.x is EOL.

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
