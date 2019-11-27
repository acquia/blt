# Next steps

Now that your new project works locally, review [BLT documentation by role](https://blt.readthedocs.io/) to learn how to perform common project tasks and integrate with third party tools.

Here are tasks that are typically performed at this stage:

* Initialize CI integration. See [Continuous Integration](ci.md).

        blt recipes:ci:pipelines:init
        # OR
        blt recipes:ci:travis:init

* Push to your upstream repo.

        git add -A
        git commit -m 'My new project is great.'
        git remote add origin [something]
        git push origin

* Ensure that you have entered a value for `git.remotes` in `blt/blt.yml`, e.g.,

        git:
          remotes:
            - bolt8@svn-5223.devcloud.hosting.acquia.com:bolt8.git

* Create and deploy an artifact. See [Deployment workflow](deploy.md).

        blt artifact:deploy

Other commonly used commands:

        # list targets
        blt

        # validate code via phpcs, php lint, composer validate, etc.
        blt validate

        # run phpunit tests
        blt tests:phpunit:run

        # ssh into vm & run behat tests
        blt tests:behat:run

        # diagnose issues
        blt doctor

        # download & require a new project
        composer require drupal/ctools:^8.3.0

        # build a deployment artifact
        blt artifact:build

        # build artifact and deploy to git.remotes
        blt artifact:deploy

        # update BLT
        composer update acquia/blt --with-dependencies

## Drush aliases

See [BLT Drush Documentation](drush.md) for more information on Drush aliases.

## Adding settings to settings.php

A common practice in Drupal is to add settings to the `settings.php` file to control things like cache backends, set site variables, or other tasks which do need a specific module. BLT provides two mechanisms to add settings to settings.php. Settings files may be added to the `docroot/sites` directory for inclusion in all sites in the codebase or settings can be added via an `includes.settings.php` in the `settings` directory of an individual  site (i.e., `docroot/sites/{site-name}/settings/includes.settings.php`). Both mechanisms allow settings to be overriden by a `local.settings.php`, to support local development.

> **Important**: When using BLT, settings should not be simply added directly to `settings.php`. This is especially true with Acquia Cloud Site Factory, which will ignore settings added directly to `settings.php`.

The first level of BLT's settings management is the `blt.settings.php` file. When sites are created, BLT adds a require line to the standard `settings.php` file which includes the `blt.settings.php` file from BLT's location in the `vendor` directory. This file then controls the inclusion of other settings files in a hierarchy. The full hierarchy of settings files used by BLT looks like this:

```
  sites/{site-name}/settings.php
    |
    ---- blt.settings.php
           |
           ---- sites/settings/*.settings.php
           |
           ---- sites/{site-name}/settings/includes.settings.php
           |       |
           |       ---- foo.settings.php
           |       ---- bar.settings.php
           |       ---- ....
           |
           ---- sites/{site-name}/settings/local.settings.php
 ```

 > **Important**: Do not edit the `blt.settings.php` file in the `vendor` directory. If you do, the next time composer update or install is run your changes may be lost. Instead, use one of the mechanisms described below.

#### Global settings for the codebase
To allow settings to be made once and applied to all sites in a codebase, BLT [globs](http://php.net/manual/en/function.glob.php) the `docroot/sites/settings` directory to find all files matching a `*.settings.php` format and adds them via [PHP require](http://php.net/manual/en/function.require.php) statements.

As not all projects will need additional global settings, BLT initially deploys a `default.global.settings.php` file into the `docroot/sites/settings` directory. To make use of this file, rename it to `global.settings.php` and settings or required files as needed.


#### Per site
On a per-site basis, BLT uses an `includes.settings.php` file in the `settings` directory of each individual site. Any settings made in that file, or other files required into it, will be added to the settings for that particular site only.

As not all projects will need additional includes, BLT initially deploys a `default.includes.settings.php` file into the site's `docroot/sites/{site_name}/settings` directory. To make use of this file, rename it to `includes.settings.php` and add the path to the file(s) which should be added.
