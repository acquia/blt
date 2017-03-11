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

Ideally, you will be using a theme that uses SASS/SCSS, a styleguide, and other tools that require compilation. Like dependencies, the compiled assets should not be directly committed to the project repository. Instead, they should be built during the creation of a production-ready build artifact.

BLT allows you to define a custom command that will be run to compile your project's frontend assets. You can specify the command in your project's `blt/project.yml` file under the `target-hooks.frontend-build` key:


    target-hooks:
      frontend-build:
        # The directory in which the command will be executed.
        dir: ${docroot}
        command: npm install.

If you need to run more than one command, you may use this feature to call a custom script:

    target-hooks:
      frontend-build:
        # The directory in which the command will be executed.
        dir: ${repo.root}
        command: ./scripts/custom/my-script.sh

This command will be executed when dependencies are built in a local or CI environment, and when a deployment artifact is generated. You may execute the command directly by calling the `frontend:build` target:

    blt frontend:build

## Updating your local environment

The project is configured to update the local environment with a local drush alias and a remote alias as defined in `blt/project.yml` or `blt/project.local.yml`. Given that these aliases match, those in `drush/site-aliases/`, you can update the site with BLT. Please see [drush/README.md](../template/drush/README.md) for details on how to create these aliases.

### Refresh: Rebuild the codebase, copy the database, and run updates

This all in one command will make sure your local is in sync with the remote site.

    blt local:refresh

You may also sync your site's remote files by setting the `-Dsync.files` variable at the command line.

    blt local:refresh -Dsync.files=true

By default, BLT sets `sync.files` to `false`. You may set `sync.files` to `true` in your `project.yml` file to perform a file sync during `local:sync` and `local:refresh` tasks by default within your project.

### Sync: Copy the database from the remote site

    blt local:sync

### Update: Run update tasks locally

    blt local:update

These tasks can be seen in `build/core/phing/tasks/local-sync.xml`. An additional script can be added at `/hooks/dev/post-db-copy/dev-mode.sh` which would run at the end of this task.
