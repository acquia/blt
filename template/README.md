# My Project

A brief description of My Project.

## Using This Template

Remove this section after initial setup!

Search for and replace the following placeholders within this file:

| Placeholder | Example |
| --- | --- |
| `#ACQUIA_CLOUD_URL` | https://cloud.acquia.com/app/develop/applications/12345678-1234-1234-12345678901234567 |
| `#GIT_PRIMARY_DEV_BRANCH` | `master` or `develop` |
| `#GITHUB_ORG` | The "org" in https://github.com/org/project |
| `#GITHUB_PROJECT` | The "project" in https://github.com/org/project |
| `#JIRA_URL` | https://org.atlassian.net/projects/PROJ |
| `#LOCAL_DEV_SITE_ALIAS` | `@example.local` |
| `#LOCAL_DEV_URL` | http://local.example.com/ |
| `#TRAVIS_URL` | https://travis-ci.com/org/PROJ |

## Getting Started

This project is based on BLT, an open-source project template and tool that enables building, testing, and deploying Drupal installations following Acquia Professional Services best practices. While this is one of many methodologies, it is our recommended methodology. 

1. Review the [Required / Recommended Skills](http://blt.readthedocs.io/en/latest/readme/skills) for working with a BLT project.
1. Ensure that your computer meets the minimum installation requirements (and then install the required applications). See the [System Requirements](http://blt.readthedocs.io/en/latest/INSTALL/#system-requirements).
1. Request access to organization that owns the project repo in GitHub (if needed).
1. Fork the project repository in GitHub.
1. Request access to the Acquia Cloud Environment for your project (if needed).
1. Setup a SSH key that can be used for GitHub and the Acquia Cloud (you CAN use the same key).
    1. [Setup GitHub SSH Keys](https://help.github.com/articles/adding-a-new-ssh-key-to-your-github-account/)
    1. [Setup Acquia Cloud SSH Keys](https://docs.acquia.com/acquia-cloud/ssh/generate)
1. Clone your forked repository. By default, Git names this "origin" on your local.
    ```
    $ git clone git@github.com:<account>/#GITHUB_PROJECT.git
1. To ensure that upstream changes to the parent repository may be tracked, add the upstream locally as well.
    ```
    $ git remote add upstream git@github.com:#GITHUB_ORG/#GITHUB_PROJECT.git
    ```
1. Install Composer Dependencies. (Warning: this can take some time based on internet speeds.)
    ```
    $ composer install
    ```
1. Setup local environment.

    BLT requires "some sort" of local environment that implements a LAMP stack. While we provide out of the box templates for Drupal VM, if you prefer you can use another tool such as Docker, Docksal, Lando, (other) Vagrant, or your own custom LAMP stack. BLT works with any local environment, however support is limited for these solutions.

    For instructions on setting up Drupal VM, [read our documentation here](http://blt.readthedocs.io/en/9.x/readme/local-development/#using-drupal-vm-for-blt-generated-projects).

1. Run the initial setup:
    ```
    $ vagrant ssh
    $ blt setup
    ```
1. Access the site and do necessary work at #LOCAL_DEV_URL by running this:
    ```
    $ drush uli
    ```

Additional [BLT documentation](http://blt.readthedocs.io) may be useful. You may also access a list of BLT commands by running this:
```
$ blt
``` 

Note the following properties of this project:
* Primary development branch: #GIT_PRIMARY_DEV_BRANCH
* Local environment: #LOCAL_DEV_SITE_ALIAS
* Local site URL: #LOCAL_DEV_URL

## Working With a BLT Project

BLT projects are designed to instill software development best practices (including git workflows). 

Our BLT Developer documentation includes an [example workflow](http://blt.readthedocs.io/en/latest/readme/dev-workflow/#workflow-example-local-development).

### Important Configuration Files

BLT uses a number of configuration (`.yml` or `.json`) files to define and customize behaviors. Some examples of these are:

* `blt/blt.yml` (formerly blt/project.yml prior to BLT 9.x)
* `blt/local.blt.yml`
* `box/config.yml` (if using Drupal VM)
* `drush/sites` (contains Drush aliases for this project)
* `composer.json` (includes required components, including Drupal Modules, for this project)

## Resources

* JIRA - #JIRA_URL
* GitHub - https://github.com/#GITHUB_ORG/#GITHUB_PROJECT
* Acquia Cloud subscription - #ACQUIA_CLOUD_URL
* TravisCI - #TRAVIS_URL
