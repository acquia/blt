# Repository architecture

“How is the code organized, and why?”

The repository architecture is driven by a set of core principles:

* Project dependencies should never be committed directly to the repository
* The code that is deployed to production should be fully validated, tested, sanitized, and free of non-production tools
* Common project tasks should be fully automated and repeatable, independent of environment

Consequently, there are a few aspects of this project’s architecture and workflow that may be unfamiliar to you.

* Drupal core, contrib modules, themes, and third parties libraries are not committed to the repository. Contrib directories that are .gitignored are populated during [build artifact](deploy.md) generation.
* The repository is never pushed directly to the cloud. Instead, changes to the repository on GitHub trigger tests to be run via [Continuous Integration](ci.md). Changes that pass testing will automatically cause a [build artifact](deploy.md) to be created and deployed to the cloud.
* [Common project tasks](project-tasks.md) are executed via a build tool (Robo) so that they can be executed exactly the same in all circumstances.

## Directory structure

The following is an overview of the purpose of each top level directory in the project template:

    root
      ├── blt      - Contains custom build config files for CI solutions
      ├── box      - Contains DrupalVM Configuration (optional, created by `blt vm`)
      ├── config   - Contains Drupal 8 configuration files
      ├── drush    - Contain drush configuration that is not site or environment specific
      ├── docroot  - The drupal docroot
      ├── hooks    - Contains Acquia Cloud hooks (optional, created by `blt recipes:cloud-hooks:init`)
      ├── patches  - Contains private patches to be used by composer.json
      ├── reports  - Contains output of automated tests; is .gitignored
      ├── tests    - Contains project-level test files and configuration
      ├── vendor   - Contains built composer dependencies; is .gitignored

## Dependency Management

All project and Drupal (module, themes, libraries) dependencies are managed via Composer. The management strategy is based on [The Drupal Project](https://github.com/drupal-composer/drupal-project).

Modules, themes, and other contributed drupal projects can be added as dependencies in the root composer.json file.

For step-by-step instructions on how to update dependencies, see [Project Tasks](project-tasks.md).
