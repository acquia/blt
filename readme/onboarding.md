# Onboarding

Here is a quick-start guide to getting your local development environment set up and getting oriented with the project standards and workflows.

## Required SAAS Access:

Please ask the project's engagement manager for access to the following SAAS services:

* JIRA
* GitHub repository
* Acquia Cloud subscription

## System Requirements

You should be able to use the following tools on the command line of your native operating system:

* [Git](https://git-scm.com/)
* [Composer](https://getcomposer.org/download/)
* PHP 5.6. PHP installation instructions:
    * [OSX](http://justinhileman.info/article/reinstalling-php-on-mac-os-x/)
    * [Windows](http://php.net/manual/en/install.windows.php)
    * [Linux](http://php.net/manual/en/install.unix.debian.php)

### Operating Systems

We highly recommend that you *do not use Windows* directly for development. Many development tools (e.g., drush, gulp, etc.) are not built or tested for Windows compatibility. Furthermore, most CI solutions (e.g., Travis CI, Drupal CI, etc.) do not permit testing on Windows OS. Similarly, BLT cannot be fully tested on Windows and is unsupported on this platform.

If you must use Windows, we recommend that:
* You have administrator access to your machine
* You execute the necessary command line functions a bash emulator such as:
    * [Git Bash](https://git-for-windows.github.io/)
    * [cmder](http://cmder.net/)
    * [cygwin](https://www.cygwin.com/)
* Run BLT inside of a Drupal-VM instance

### Networking considerations

Building project dependencies requires that your local machine make HTTP and HTTPS requests to various software providers on the internet. Please ensure that your local and network level security settings permit this to happen.

If you need to make requests via a proxy server, please [configure git to use a proxy](http://stackoverflow.com/a/19213999). This will cover all git based requests made by Composer.

## Initial Setup

1. [Fork](https://help.github.com/articles/fork-a-repo) the primary GitHub repository
1. Clone your fork to your local machine:

         git clone git@github.com:username/project-repo.git
         git remote add upstream git@github.com:acquia-pso/project-repo.git

1. If your project uses separate `master` and `develop` branches, checkout the `develop` branch: `git checkout develop`
1. Run `composer install` (you must already have Composer installed).
1. Install `blt` alias: `composer blt-alias`

If your project uses a virtual development environment such as DrupalVM:

1. Make sure you have installed any prerequisites. For DrupalVM, see the [quick start guide](https://github.com/geerlingguy/drupal-vm#quick-start-guide).
1. Start your virtual machine: `vagrant up`
1. Build and install the Drupal installation: `blt setup`

If your project does not use a virtual development environment:

1. Run `blt setup:drupal:settings` This will generate `docroot/sites/default/settings/local.settings.php` and `docroot/sites/default/local.drushrc.php`. Update these with your local database credentials and your local site URL.
1. Run `blt local:setup`. This will build all project dependencies and install drupal.
1. Create and edit your local drush alias file. Copy `drush/site-aliases/example.local.aliases.drushrc.php` to `drush/site-aliases/local.aliases.drushrc.php`. Edit the new alias file with your local path.

Please see [Local Development](local-development.md) for detailed information on setting up a local \*AMP stack or virtual development environment.

## Ongoing development
As development progresses, you can use the following commands to keep your local environment up to date:

- Run `blt local:setup` to rebuild the codebase and reinstall your Drupal site (most commonly used early in development).
- Run `blt local:refresh` to rebuild the codebase, import a fresh DB from a remote environment, and run schema/configuration updates (most commonly used later in development).

Each of these commands is simply a wrapper for a number of more granular commands that can be run individually if desired (for instance, `blt local:update` just runs database updates and imports configuration changes). For a full list of available project tasks, run `blt -l`. See [Project Tasks](project-tasks.md) for more information.

### Local Git Configuration

For readability of commit history, set your name and email address properly:

    git config user.name "Your Name"
    git config user.email your-email-address@example.com

Ensure that your local email address correctly matches the email address for your Jira account.

## Updating you local environment

The project is configured to update the local environment with a local drush alias and a remote alias as defined in `project.yml` or `project.local.yml`. Given that these aliases match, those in `drush/site-aliases/`, you can update the site with BLT.

[Local Development Tasks](project-tasks.md#local-tasks)

## GitHub Configuration

In order to more easily identify developers in a project, please be sure to set a name and profile picture in your GitHub profile.

When working with GitHub, the [hub](https://github.com/github/hub) utility can be helpful when managing forks and pull requests. Installing hub largely depends on your local environment, so please follow the [installation instructions](https://github.com/github/hub#installation) accordingly.

## Next steps

Review review the following documentation:

* [Repository architecture](repo-architecture.md): “how is the code organized, and why?”
* [Running project tasks](project-tasks.md): “how do I _____ on my local machine?”
* [Workflow](dev-workflow.md): “I wrote code, how does it get from here to there?”
* [Automated testing](readme/testing.md): “how do I write / run them, and why should care?”
