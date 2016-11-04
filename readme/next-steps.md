# Next steps

Now that your new project works locally, review [BLT documentation by role](https://blt.readthedocs.io/) to learn how to perform common project tasks and integrate with third party tools.

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
