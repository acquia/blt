# BLT

[![Build Status](https://travis-ci.org/acquia/blt.svg?branch=8.x)](https://travis-ci.org/acquia/blt) [![Documentation Status](https://readthedocs.org/projects/blt/badge/?version=8.x)](http://blt.readthedocs.io/en/8.x/?badge=8.x) [![Packagist](https://img.shields.io/packagist/v/acquia/blt.svg)](https://packagist.org/packages/acquia/blt)

BLT (Build and Launch Tool) is a tool that generates new Drupal projects using a standardized template derived from Acquia Professional Services' best practices.

You can find all BLT documentation on [Read the Docs](http://blt.readthedocs.io):

* [Latest release documentation](http://blt.readthedocs.io/en/stable/)
* [Latest documentation (8.x / 8.x-dev)](http://blt.readthedocs.io/en/latest/)

## Getting started

See [INSTALL.md](INSTALL.md) to:

* [Create a new project with BLT](https://github.com/acquia/blt/blob/8.x/INSTALL.md#creating-a-new-project-with-blt)
* [Add BLT to an existing project](https://github.com/acquia/blt/blob/8.x/INSTALL.md#adding-blt-to-an-existing-project)
* [Update BLT](https://github.com/acquia/blt/blob/8.x/INSTALL.md#updating-blt)

## Videos

* [BLT Project Creation](https://www.youtube.com/watch?v=KBwS0fsmXRs)
* [Deploying to Acquia Cloud](https://www.youtube.com/watch?v=jjnPMvZ2x-c)

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

## Features

* [Git Hooks](scripts/git-hooks)
    * pre-commit: Checks for Drupal coding standards compliance
    * commit-msg: Check for proper formatting and syntax
* [Testing Framework](template/tests).
    * Behat: default `local.yml` configuration, example tests, `FeatureContext.php`
    * PHPUnit: default tests for ensuring proper functioning of BLT provided components
* [Project tasks](readme/project-tasks.md)
    * Executing tests and validating code
    * Building dependencies
        * Management of Drupal core, contrib, and third party libraries via Composer
        * Building front end assets. E.g, via gulp, npm, bower, etc.
    * (Re)installation of Drupal
      * Configuration import
* [Artifact Generation](readme/deploy.md)
    * Building production-only dependencies
    * Sanitation of production code
* [Continuous Integration & Deployment](readme/ci.md)
    * [Acquia Pipelines](https://dev.acquia.com/request-invite-acquia-pipelines) (coming soon)
    * [Travis CI](https://travis-ci.com)
    * [GitHub](https://github.com)

# Support and contribution

BLT is provided as an open source tool in the hope that it will enabled developers to easily generate new Drupal projects that conform to Acquia Professional Services' best practices.

Please feel free to contribute to the project or file issues via the GitHub issue queue. See [CONTRIBUTING.md](CONTRIBUTING.md) for contribution guidelines and instructions.

# License

Copyright (C) 2016 Acquia, Inc.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License version 2 as published by the Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
