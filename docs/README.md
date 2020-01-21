# BLT

![BLT logo of stylized sandwich](https://github.com/acquia/blt/raw/11.x/docs/_static/blt-logo.png)

[![Build Status](https://travis-ci.com/acquia/blt.svg?branch=11.x)](https://travis-ci.com/acquia/blt) [![Packagist](https://img.shields.io/packagist/v/acquia/blt.svg)](https://packagist.org/packages/acquia/blt)

BLT (Build and Launch Tool) provides an automation layer for testing, building, and launching Drupal 8 applications.

You can find compiled BLT documentation on [docs.acquia.com](https://docs.acquia.com/blt).

## Getting started

See [Installing Acquia BLT](https://docs.acquia.com/blt/install/) for a list of prequisites and links to instructions for [creating a new project](https://docs.acquia.com/blt/install/creating-new-project/), [adding BLT to an existing project](https://docs.acquia.com/blt/install/adding-to-project/), and [updating BLT](https://docs.acquia.com/blt/install/updating-blt/).

## Videos

* [BLT Project Creation](https://www.youtube.com/watch?v=KBwS0fsmXRs)
* [Deploying to Acquia Cloud](https://www.youtube.com/watch?v=jjnPMvZ2x-c)

## Releases and versioning

BLT generally has two supported releases at any given time, each corresponding to a major [semantic version](https://semver.org/). The newest supported major version will receive bug fixes and new features, while the penultimate major version will receive only critical bug and security fixes for six months. Major and minor releases with new features and bug fixes occur on the first Wednesday of every month. Security and bug-fix releases occur only when necessary on the third Wednesday of every month.

This is intended to coincide with the [Drupal core release cycle](https://www.drupal.org/core/release-cycle-overview), so that users can continue to use a single BLT major release through the lifecycle of a Drupal core minor release.

### Release support status

| BLT version | Support status        | End of life    |  Drupal versions* | Drush versions |
|-------------|-----------------------|----------------|-------------------|----------------|
| **11.x**    | **Supported, stable** | **>=Dec 2020** | **8.7\*\*, 8.8**      | **>=10.0**     |
| 10.x        | Bug fixes only        | May 2020       | 8.7, 8.8          | >=9.5.0        |
| <=9.2.x     | Unsupported           | Dec 2019       | 8.6, 8.7          | >=9.4.0        |

*When any upstream package release stops being supported by its maintainer, BLT will cease supporting that release as well. For instance, as of December 2019, BLT 10.x will no longer support Drupal 8.6, and will instead support Drupal 8.7 and 8.8  in accordance with [Drupal security policy](https://www.drupal.org/drupal-security-team/general-information).

**Existing Drupal 8.7 projects can use BLT 11. However, when creating _new_ projects, BLT 11 only supports Drupal 8.8 due to its dependency on Composer Scaffold.

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

* [Local Git Hooks](https://github.com/acquia/blt/tree/11.x/scripts/git-hooks)
    * pre-commit: Checks for Drupal coding standards compliance
    * commit-msg: Check for proper formatting and syntax
* [Testing Framework](https://github.com/acquia/blt/tree/11.x/template/tests).
    * Behat: default `local.yml` configuration, example tests, `FeatureContext.php`
    * PHPUnit: default tests for ensuring proper functioning of BLT provided components
* [Commands to automate project tasks](https://docs.acquia.com/blt/developer/project-tasks/), like:
    * Test execution
    * Frontend asset compilation
    * Syncing environments
* [Deployment Artifact Generation](https://docs.acquia.com/blt/tech-architect/deploy/)
    * Building production-only dependencies
    * Sanitation of production code
* [Continuous Integration & Deployment](https://docs.acquia.com/blt/tech-architect/ci/)
    * [Acquia Pipelines](https://dev.acquia.com/request-invite-acquia-pipelines)
    * [Travis CI](https://travis-ci.com)
    * [GitHub](https://github.com)

# Support and contribution

BLT is provided as an open source tool in the hope that it will enable developers to easily generate new Drupal projects that conform to Acquia Professional Services' best practices. See [Acquia BLT support](https://docs.acquia.com/blt/support/) to find resources that are available for support with BLT issues.

Please feel free to contribute to the project or file issues via the GitHub issue queue. See [Contributing to Acquia BLT](https://docs.acquia.com/blt/contributing/) for contribution guidelines and instructions.
# License

Copyright (C) 2016 Acquia, Inc.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
