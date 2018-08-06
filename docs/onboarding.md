# Onboarding

Here is a quick-start guide to getting your local development environment set up and getting oriented with the project standards and workflows.

## Before you start...

You have probably been linked to this documentation by a project that is using BLT to accelerate its development, testing, and deployment. While we strongly recommend exploring all of the BLT documentation, here's what's most important to do and know before getting started:

* BLT is distributed as a Composer package. This means that the project you are working on requires BLT as a dependency in its composer.json file. This also means that you don't need to install or configure BLT globally on your machine, or as a separate tool--simply run `composer install` on the parent project and install a tiny bash alias (as described below), and you're good to go.
* You will need some project-specific information to set up your local environment, specifically whether you are using a virtual development environment (e.g., DrupalVM), and the name of your mainline development branch (`develop` or `master`). This should be referenced in your project's README.
* If you need help, check with your project team first, since they may have already encountered any issue you are experiencing. Then post an issue in the [BLT issue queue](https://github.com/acquia/blt/issues). The issue queue isn't only for bugs--we welcome feedback on all aspects of the developer experience.
* You should verify that your local system and network meet [System requirements](INSTALL.md).
* Because BLT makes use of a variety of best practice development tools and processes (Composer, Git, etc...), you should verify that you have the necessary [skillset(s)](skills.md) to develop with BLT.

## Initial Setup

1. Verify that your system meets the [system requirements for BLT](INSTALL.md)
1. [Fork](https://help.github.com/articles/fork-a-repo) the primary GitHub repository for the project you are developing
1. Clone your fork to your local machine (by convention, BLT refers to your fork as "origin" and the primary repo as "upstream"):

         git clone git@github.com:username/project-repo.git
         git remote add upstream git@github.com:acquia-pso/project-repo.git

1. If your project uses separate `master` and `develop` branches, checkout the `develop` branch: `git checkout develop`
1. Run `composer install` (you must already have Composer installed)
1. Install `blt` alias: `composer run-script blt-alias`. At this point you might need restart your shell in order for the alias work

If your project uses a virtual development environment such as Drupal VM:

1. Make sure you have installed any prerequisites. For DrupalVM, see the [quick start guide](https://github.com/geerlingguy/drupal-vm#quick-start-guide).
1. If this is your first time using this project's VM on your machine, execute `blt vm` to provision the VM and set it as the default local development environment. If you've already run `blt vm` at least once, you can just use `vagrant up` to provision the VM.
1. SSH into the VM: `vagrant ssh`
1. Build and install the Drupal installation: `blt setup`

If your project does not use a virtual development environment:

1. Setup your local LAMP stack with the webroot pointing at you project's `docroot` directory.
1. Run `blt blt:init:settings` This will generate `docroot/sites/default/settings/local.settings.php` and `docroot/sites/default/local.drush.yml`. Update these with your local database credentials and your local site URL.
1. Run `blt setup`. This will build all project dependencies and install drupal.

Please see [Local Development](local-development.md) for more information on setting up a local \*AMP stack or virtual development environment.

## Ongoing development

As development progresses, you can use the following commands to keep your local environment up to date:

- Run `blt setup` to rebuild the codebase and reinstall your Drupal site (most commonly used early in development).
- Run `blt drupal:sync` to rebuild the codebase, import a fresh DB from a remote environment, and run schema/configuration updates (most commonly used later in development).

Each of these commands is simply a wrapper for a number of more granular commands that can be run individually if desired (for instance, `blt drupal:update` just runs database updates and imports configuration changes). For a full list of available project tasks, run `blt`. See [Project Tasks](project-tasks.md) for more information.

### Local Git Configuration

For readability of commit history, set your name and email address properly:

    git config user.name "Your Name"
    git config user.email your-email-address@example.com

Ensure that your local email address correctly matches the email address for your Jira account.

## Updating your local environment

The project is configured to update the local environment with a local drush alias and a remote alias as defined in `blt/blt.yml` or `blt/local.blt.yml`. Given that these aliases match those in `drush/sites/`, you can update the site with BLT.

[Local Development Tasks](project-tasks.md#local-tasks)

## GitHub Configuration

In order to more easily identify developers in a project, please be sure to set a name and profile picture in your GitHub profile.

When working with GitHub, the [hub](https://github.com/github/hub) utility can be helpful when managing forks and pull requests. Installing hub largely depends on your local environment, so please follow the [installation instructions](https://github.com/github/hub#installation) accordingly.

## Next steps

Review [BLT documentation by role](http://blt.readthedocs.io/) to learn how to perform common project tasks and integrate with third party tools.
