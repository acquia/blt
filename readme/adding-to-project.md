# Adding BLT to an existing project

To add BLT to a pre-existing Drupal project, do the following:

1. Ensure that your project directory structure is Acquia-cloud compatible by asserting that the Drupal root is in a top-level folder called `docroot`.
1. If you currently manage your dependencies via Composer, ensure that they are all up to date via `composer update`. Assert that these updates do not break your project.
1. `cd` into your existing project directory.
1. Add BLT via composer:

        composer require acquia/blt:^8.3

1. Continue following instructions for step 2 and beyond in [Creating a new project with BLT](../INSTALL.md#creating-a-new-project-with-blt).
