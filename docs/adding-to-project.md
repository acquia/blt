# Adding BLT to an existing project

BLT is normally used to create new projects from scratch, but you can also add BLT to an existing project to get all of the [same benefits](https://github.com/acquia/blt/tree/10.x#features) as a normal BLT project.

Adding BLT to an existing project can be more complex than creating a new site with BLT since BLT includes a project template and expects the files in your project to match the structure and contents of this template.

BLT will attempt to generate any missing files when it is installed, but it can't account for every possible project structure and some manual reconciliation may be required. Adhering closely to the prerequisites and instructions below should ensure a good outcome.

Prerequisites:

1. The Drupal root must be in a top-level `docroot` directory.
1. You must **already use Composer to manage site dependencies**. If you don't, please see [Using Composer to manage Drupal site dependencies](https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies) and modify your project accordingly.
1. Ensure that your dependencies are all up to date via `composer update`. Assert that these updates do not break your project.

To add BLT to a pre-existing Drupal project, do the following:

1. `cd` into your existing project directory.
1. Set Composer's `minimum-stability` to `dev` and `prefer-stable` to `true`:

        composer config minimum-stability dev
        composer config prefer-stable true

1. Add BLT via composer:

        composer require acquia/blt:^10.0 --no-update
        composer update

1. Replace your `.gitignore` file with BLT's template `.gitignore`, then re-add any project-specific overrides:

        cp vendor/acquia/blt/subtree-splits/blt-project/.gitignore .
        
1. (optional, but recommended) Replace your `composer.json` with BLT's template `composer.json`, and re-add any packages or other configuration that you wish to preserve from your existing `composer.json`:

        cp vendor/acquia/blt/subtree-splits/blt-project/composer.json .
        
1. BLT will have placed a number of files in your project directory that were likely not present before. Be sure to review these, as well as the files listed above, and commit them to Git.
1. Run `blt doctor` to diagnose any potential remaining issues that require manual intervention.
1. Continue following instructions after the `composer create-project` command in [Creating a new project with BLT](creating-new-project.md).
