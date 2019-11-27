.. include:: ../../common/global.rst

Repository architecture
=======================

The repository architecture is based on the following set of core principles:

-  Project dependencies should never be committed directly to the repository.
-  The code that is deployed to production should be fully validated, tested,
   sanitized, and free of non-production tools.
-  Common project tasks should be fully automated and repeatable, independent
   of the environment.

Consequently, there are some aspects of a project's architecture and
workflow that may be unfamiliar to you:

-  Drupal core, contributed (contrib) modules, themes, and third-party
   libraries are not committed to the repository. Contrib module directories
   that are ignored with ``.gitignore`` are populated during :doc:`build
   artifact </blt/tech-architect/deploy/>` generation.
-  The repository is never pushed directly to the cloud. Instead, changes to
   the repository on GitHub trigger tests to be run using
   :doc:`/blt/tech-architect/ci/`. Changes that pass testing will cause a
   :doc:`build artifact </blt/tech-architect/deploy/>` to be created and then
   deployed to the cloud.
-  :doc:`Common project tasks </blt/developer/project-tasks/>` are executed
   using a build tool (such as Robo) allowing them to execute exactly the same
   in all circumstances.


.. _blt-repo-arch-directory-structure:

Directory structure
-------------------

The following is an overview of the purpose of each top level directory
in the project template:

.. code-block:: text

   root
     ├── blt      - Contains custom build config files for CI solutions
     ├── box      - Contains DrupalVM Configuration (optional, created by
     |              `blt vm`)
     ├── config   - Contains Drupal 8 configuration files
     ├── drush    - Contain drush configuration that is not site or
     |              environment specific
     ├── docroot  - The Drupal docroot
     ├── hooks    - Contains Acquia Cloud hooks (optional, created by
     |              `blt recipes:cloud-hooks:init`)
     ├── patches  - Contains private patches to be used by composer.json
     ├── reports  - Contains output of automated tests; is .gitignored
     ├── tests    - Contains project-level test files and configuration
     ├── vendor   - Contains built composer dependencies; is .gitignored


.. _blt-repo-arch-dependency-management:

Dependency management
---------------------

All project and Drupal (including module, themes, and libraries) dependencies
are managed using Composer. This management strategy is based on the `Drupal
Project <https://github.com/drupal-composer/drupal-project>`__.

You can add modules, themes, and other contributed Drupal projects as
dependencies in the root ``composer.json`` file.

For step-by-step instructions about how to update dependencies, see
:doc:`/blt/developer/project-tasks/`.

.. Next review date 20200422
