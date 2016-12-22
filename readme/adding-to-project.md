# Adding BLT to an existing project

Adding BLT to an existing project can be much more complex than simply creating a new site with BLT. There are a few reasons for this:

1. BLT expects your project to have a particular directory structure and set of files.
1. You may have to resolve conflicts between your existing dependencies and BLT's dependencies.

Prerequisites:

1. Ensure that your project directory structure is Acquia-cloud compatible by asserting that the Drupal root is in a top-level folder called `docroot`.
1. You must **already user Composer to manage site dependencies**. If you don't, please see [Using Composer to manage Drupal site dependencies](https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies) and modify your project accordingly. 
1. Ensure that your dependencies are all up to date via `composer update`. Assert that these updates do not break your project.

To add BLT to a pre-existing Drupal project, do the following:

1. `cd` into your existing project directory.
1. Add BLT via composer:

        composer require acquia/blt:^8.3 --no-update
        composer update

1. Continue following instructions for step 2 and beyond in [Creating a new project with BLT](../INSTALL.md#creating-a-new-project-with-blt).
