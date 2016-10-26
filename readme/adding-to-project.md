# Adding BLT to an existing project

To add BLT to a pre-existing Drupal project, do the following:

1. Ensure that your project directory structure is Acquia-cloud compatible by asserting that the Drupal root is in a top-level folder called `docroot`.
1. If you currently manage your dependencies via Composer, ensure that they are all up to date via `composer update`. Assert that these updates do not break your project.
1. `cd` into your existing project directory.
1. Add BLT via composer:

        composer require acquia/blt:^8.3

1. Continue following instructions for step 2 and beyond in [Creating a new project with BLT](../INSTALL.md#creating-a-new-project-with-blt).

# Next steps

Now that your new project works locally, review [BLT documentation by role](https://http://blt.readthedocs.io/) to learn how to perform common project tasks and integrate with third party tools.

Here are tasks that are typically performed at this stage:

* Initialize CI integration. See [Continuous Integration](ci.md).

        blt ci:pipelines:init
        # OR
        blt ci:travis:init

* Push to your upstream repo.

        git add -A
        git commit -m 'My new project is great.'
        git remote add origin [something]
        git push origin

* Create and deploy an artifact. See [Deployment workflow](deploy.md).

        # Ensure git.remotes is set in project.yml
        blt deploy

Other commonly used commands:

        # list targets
        blt

        # validate code via phpcs, php lint, composer validate, etc.
        blt validate

        # run phpunit tests
        blt tests:phpunit

        # ssh into vm & run behat tests
        drush @[project.machine_name].local ssh
        blt tests:behat

        # diagnose issues
        blt doctor

        # download & require a new project
        composer require drupal/ctools:^8.3.0

        # build a deployment artifact
        blt deploy:build

        # build artifact and deploy to git.remotes
        blt deploy

        # update BLT
        composer update acquia/blt --with-dependencies
