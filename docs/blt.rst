.. include:: common/global.rst

Acquia BLT
==========

.. toctree::
   :hidden:
   :glob:

   /blt/release-notes/
   /blt/install
   /blt/developer
   /blt/tech-architect
   /blt/extending-blt/
   /blt/plugins
   /blt/contributing/
   /blt/support/

Acquia BLT (Build and Launch Tool), `available on GitHub
<https://github.com/acquia/blt>`__, provides an automation layer for testing,
building, and launching Drupal 8 applications. Acquia BLT generates new Drupal
projects using a standardized template based on Acquia Professional
Services' best practices.

.. container:: message-status

   **QUICK LINKS:**
   :doc:`Install </blt/install/>`  |
   :doc:`Release notes </blt/release-notes/>`  |
   :doc:`Developer resources </blt/developer/>`  |
   :doc:`Tech Architect resources </blt/tech-architect/>`  |
   :doc:`Getting support </blt/support/>`

To improve efficiency and collaboration across Drupal projects, Acquia BLT
provides both a common suite of tools and standardized structure. The tools
and structure will help developers reduce incidents of duplicated work, speed
up project configuration, and onboard new developers faster.

Using Acquia BLT for your Drupal projects will help you meet the following
goals during your development cycles:

-  Provide a standard project template for Drupal based projects
-  Provide tools automating the configuration and maintenance work for
   projects
-  Document and enforce Drupal standards and best practices through default
   configuration, automated testing, and continuous integration

Acquia BLT's scope is discretely defined. It's *not* intended to provide:

-  Drupal application features such as workflow, media, layout, and
   prefabricated content types
-  A local hosting environment
-  A replacement for good judgement (as with Drupal, it leaves you the freedom
   to make mistakes)

.. _blt-features:

Features
--------

Acquia BLT offers the following features for your organization's use:

-  :doc:`Local Git hooks </blt/developer/git-hooks/>`: Evaluate formatting,
   syntax, and compliance with Drupal coding standards.
-  :doc:`Testing frameworks </blt/developer/testing/>`: Provides default
   configurations for Behat and PHPUnit.
-  :doc:`Project automation tasks </blt/developer/project-tasks/>`: Includes
   commands for syncing environments, compiling front-end assets, and
   executing tests.
-  :doc:`Deployment artifact generation </blt/tech-architect/deploy/>`:
   Includes building production-only dependencies and sanitizing
   production-environment code.
-  :doc:`Continuous integration and deployment tools
   </blt/tech-architect/ci/>`: Supports both the Acquia Cloud pipelines
   feature and Travis CI.


.. _blt-getting-started:

Getting started
---------------

To get started with Acquia BLT, review :doc:`Acquia BLT installation
instructions </blt/install/>`, and then review the following usage
documentation:

-  :doc:`/blt/developer/onboarding/`: How to get started on project work.
-  :doc:`/blt/developer/repo-architecture/`: How code is organized, and why.
-  :doc:`/blt/developer/project-tasks/`: How to complete tasks on your local
   computer.
-  :doc:`/blt/developer/dev-workflow/`: How to contribute your code to Acquia
   BLT.
-  :doc:`/blt/developer/testing/`: How to write and run tests, and why you
   should care.
-  :doc:`/blt/tech-architect/deploy/`
-  :doc:`/blt/tech-architect/release-process/`
-  :doc:`/blt/tech-architect/ci/`
-  :doc:`/blt/tech-architect/os-contribution/`


.. _blt-videos:

Videos
------

-  `Acquia BLT project creation
   <https://www.youtube.com/watch?v=KBwS0fsmXRs>`__
-  `Deploying to Acquia Cloud <https://www.youtube.com/watch?v=jjnPMvZ2x-c>`__


.. _blt-releases-versioning:

Releases and versioning
-----------------------

Acquia BLT typically has two supported releases at any given time, each
corresponding to a major `semantic version <https://semver.org/>`__. The
newest supported major version of Acquia BLT will receive bug fixes and new
features, and the immediately previous major version will receive only
critical bug and security fixes for the next six months. Acquia will release
updates for Acquia BLT based on the following schedule for major and minor
releases:

*  *New features and bug fixes*: The first Wednesday of every month.
*  *Security and bug-fix releases*: The third Wednesday of every month (as
   necessary).

This schedule coincides with the `Drupal core release cycle
<https://www.drupal.org/core/release-cycle-overview>`__, allowing the use
of a single Acquia BLT major release throughout the lifecycle of a Drupal
core minor release.


.. _blt-release-support-status:

Release support status
~~~~~~~~~~~~~~~~~~~~~~

.. list-table::
   :widths: 20 30 25 25
   :header-rows: 1
   :class: verticaltable

   * - Acquia BLT version
     - Support status
     - Drupal versions
     - Drush versions
   * - 10.x
     - Supported, stable
     - 8.6 or greater
     - 9.5.0 or greater
   * - 9.2.x
     - Bug fixes only
     - 8.6 or greater
     - 9.4.0 or greater
   * - 9.x or earlier
     - Unsupported
     - 8.5
     - 9.1.0 or greater

.. note::

    When the maintainer of a particular release of any package (such as
    Drupal or PHP) stops supporting that package, Acquia may end its
    support for that release in Acquia BLT. For example, as of December
    2019, Acquia BLT 10.x will stop supporting Drupal 8.6, and will instead
    support Drupal 8.7 or greater, based on the Drupal security policy.

For information about end-of-life announcements for Acquia's products, see
:doc:`/support/eol/`.


.. _blt-contributing:

Contributing to Acquia BLT
--------------------------

If you would like to help improve Acquia BLT, file issues from its GitHub
issue queue. For contribution guidelines and instructions, see
:doc:`/blt/contributing/`.


License
-------

This program is free software: you can redistribute it and / or modify it under
the terms of the GNU General Public License version 2 as published by the
Free Software Foundation.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

.. Next review date 20200613
