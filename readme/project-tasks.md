# Project Tasks

“how do I _____ on my local machine?”

## (re)Install Drupal

Pre-requisites to installation:

1. Ensure that `docroot/sites/default/settings/local.settings.php` exists by executing `blt setup:drupal:settings`.
1. Verify that correct local database credentials are set in `local.settings.php`.
1. Ensure that project dependencies have already been built via `blt setup:build`

To re-install Drupal, execute: `blt setup:drupal:install`. Note that this will drop the existing database tables and install Drupal from scratch!

## Add, update, or patch a dependency

Please [dependency management](dependency-management.md) for all information on managing core and contributed packages for your project.

## Deploy to cloud

Please see [Deploy](deploy.md) for a detailed description of how to deploy to Acquia Cloud.

## Run tests & code validation

Please see [testing.md](testing.md) for information on running tests.

To execute PHP codesniffer and PHP lint against the project codebase, run:

    blt validate:all

## Build front end assets

Please see [Frontend](frontend.md) for information about compiling front end assets and executing front and build processes.

## Updating your local environment

The project is configured to update the local environment with a local drush alias and a remote alias as defined in `blt/project.yml` or `blt/project.local.yml`. Given that these aliases match, those in `drush/site-aliases/`, you can update the site with BLT. Please see [drush/README.md](../template/drush/README.md) for details on how to create these aliases.

### Refresh: Rebuild the codebase, copy the database, and run updates

This all in one command will make sure your local is in sync with the remote site.

    blt sync:refresh

This will sync your site and execute all necessary updates afterwards (cache clears, database updates, config imports).

By default, BLT will not sync your public and private files directories. However, you may set `sync.files` to `true` in your `project.yml` file to perform a file sync during `sync:refresh` tasks by default
within your project.

### Multisite

If you are using multisite, you may refresh every single multisite on your local machine by running:

    blt sync:refresh:all

### Sync: Copy the database from the remote site

    blt sync

This will copy and database (and files if sync.files is set to true) but will not execute any updates afterwards.

### Update: Run update tasks locally

    blt setup:update

This will execute various update commands (cache clears, db updates, config imports) to bring the local database in light with your codebase (i.e., exported config).
