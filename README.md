# BLT

[![Build Status](https://travis-ci.org/acquia/blt.svg?branch=8.x)](https://travis-ci.org/acquia/blt)

BLT (Build and Launch Tool) is a tool that generates new Drupal projects using a standardized template derived from Acquia Professional Services' best practices.

## License

Copyright (C) 2016 Acquia, Inc.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

## Philosophy and Purpose

BLT is designed to improve efficiency and collaboration across Drupal projects by providing a common set of tools and standardized structure. It was born out of the need to reduce re-work, project set up time, and developer onboarding time.

Its explicit goals are to:

* Provide a standard project template for Drupal based projects
* Provide tools that automate much of the setup and maintenance work for projects
* Document and enforce Drupal standards and best practices via default configuration, automated testing, and continuous integration

It scope is discretely defined. It is *not* intended to provide:

* Drupal application features (e.g., workflow, media, layout, pre-fabbed content types, etc.)
* A local hosting environment
* A replacement for good judgement (as with Drupal, it leaves you the freedom to make mistakes)

## Creating a new project with BLT

It isn’t accurate to say that you can “install” BLT. Rather, you can use BLT to generate a new project. Within that project, you can then perform common project tasks like build dependencies, install Drupal, run tests, etc.

Follow the instructions in [INSTALL.md](INSTALL.md) to generate a new project using BLT.

## Features

* [Documentation templates](template/README.md)
* [Git Hooks](template/scripts/git-hooks)
    * pre-commit: Checks for Drupal coding standards compliance
    * commit-msg: Check for proper formatting and syntax
* [Acquia Cloud Hooks](template/hooks). Example integrations for third party services such as:
    * Slack
    * New Relic
    * HipChat
* [Testing Framework](template/tests).
    * Behat: default `local.yml` configuration, example tests, `FeatureContext.php`
    * PHPUnit: default tests for ensuring proper functioning of BLT provided components
* [Project tasks](template/readme/project-tasks.md)
    * Executing tests and validating code
    * Building dependencies
    * (Re)installation of Drupal
    * Production-safe artifact generation and deployment
* [Continuous Integration](template/build/README.md)
    * Travis CI
    * GitHub

## Keeping BLT projects up-to-date

BLT has been converted into a Composer package.

If you are using a version BLT that was not installed using Composer, perform the following steps to upgrade to the newest version:

```
composer require acquia/blt:~8
./vendor/acquia/blt/scripts/blt/convert-to-composer.sh
```

If your project is already using BLT via Composer, perform the following steps:

```
composer update acquia/blt
blt update
```

# License, support, and contribution

BLT is provided as an open source tool in the hope that it will enabled developers to easily generate new Drupal projects that conform to Acquia Professional Services' best practices.

Please feel free to contribute to the project or file issues via the GitHub issue queue. See [CONTRIBUTING.md](CONTRIBUTING.md) for contribution guidelines and instructions.
