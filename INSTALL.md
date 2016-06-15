## Creating a new project with BLT

The following high-level steps will be required to generate a new, fully functioning site using BLT:

1. Ensure your native OS meets minimum requirements
1. Clone BLT to your local machine
1. Generate and modify configuration files for your new project
1. Use BLT’s “installer” to generate a new site
1. Setup a local *AMP stack
1. Build your new project’s dependencies and install locally

## System Requirements

You should be able to use the following tools on the command line of your native operating system:

* [Git](https://git-scm.com/)
* [Composer](https://getcomposer.org/download/)
* PHP 5.6. PHP installation instructions:
    * [OSX](http://justinhileman.info/article/reinstalling-php-on-mac-os-x/)
    * [Windows](http://php.net/manual/en/install.windows.php)
    * [Linux](http://php.net/manual/en/install.unix.debian.php)

## Prepare BLT installer

* Clone BLT to your local machine on your native OS:
  `git clone https://github.com/acquia/blt.git` 

# Generate and modify configuration files

From the BLT repository’s root directory, run `./blt.sh configure`. This will create your project-specific configuration files. After running, the following files should exist in the BLT root directory:

* project.yml
* local.settings.php

You will need to open these files and modify their values with settings for your project. At a minimum, you must set the following configuration items:

* Local site DB credentials: `$databases` in local.settings.php

At this point, you likely have not configured your local *AMP stack for your new site. That’s ok. Simply enter the local URL and local DB credentials that you intend to use when your *AMP stack is up and running.

## Create a new project

BLT’s “installer” will do the following:
* Create new project directory (sibling of the BLT repository)
* Copies BLT template files to the new directory
* Replaces tokens in copied files with project-specific strings
* Removes installation artifacts

Run `./blt.sh create` to do all the things!

Once it’s completed, __change directories to your new project directory. All subsequent steps will happen inside your new project. You have left the BLT repository behind.__

## Modifying project files

This is an optional step. Important files that you may want to modify include:

* composer.json. Note that Drupal core, contrib, and third party dependencies are all managed here.
* Project’s root README.md.
* Other project documentation in the readme directory.

Note that all of the steps from this point forward are the same steps that would be used by a newly onboarded developer setting up your existing project on their local machine for the first time.

## Set up your \*AMP stack

Before building your project dependencies and installing Drupal, you must have a fully functional \*AMP stack on your local machine. BLT intentionally does not provide this local development environment--that is outside of the scope of BLT’s intended responsibilities. It does, however, make recommendations for which tools you should use to manage your stack.

Please see [Local Development](template/readme/local-development.md) for more information on setting up your \*AMP stack:
  * [Acquia Dev Desktop](template/readme/local-development.md#using-acquia-dev-desktop-for-blt-generated-projects)  
  * [Drupal VM](template/readme/local-development.md#using-drupal-vm-for-blt-generated-projects)
  * [Other](https://github.com/acquia/blt/blob/8.x/template/readme/local-development.md#alternative-local-development-environments)

When you have completed setting up your local \*AMP stack, double check that the following pieces of information are still correct:

* Local site DB credentials: `$databases` in docroot/sites/default/settings/local.settings.php
* Local site URL: `$options[‘uri’]` in docroot/sites/default/local.drushrc.php

## Build your project’s dependencies and install Drupal

Run the following command from the project root: `./blt.sh local:setup`. This will do a lot of things for you, including:

* Building dependencies
* Installing local git hooks
* Generating local.yml for Behat
* Installing Drupal locally

When this task is complete, you should have a fully functioning Drupal site on your local machine. You can login to the site by running `drush uli`.

Note that all common project tasks are executed through `blt.sh` in your project’s root directory. This file simply passes arguments through to Phing, which manages all task automation. For a full list of available tasks, run `./blt.sh -l`.

## Next Steps

Now that your new project works locally, you’ll want to integrate with with your SAAS tools (GitHub, TravisCI, Jenkins, etc.) and your Acquia Cloud subscription.

See the following documents for more detailed instructions on those tasks:

* Configure your CI solution @todo link
* Deploy to Acquia Cloud @todo link
