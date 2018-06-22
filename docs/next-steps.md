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

* Ensure that you have entered a value for `git.remotes` in `blt/project.yml`. E.g.,

        git:
          remotes:
            - bolt8@svn-5223.devcloud.hosting.acquia.com:bolt8.git

* Create and deploy an artifact. See [Deployment workflow](deploy.md).

        blt deploy

Other commonly used commands:

        # list targets
        blt

        # validate code via phpcs, php lint, composer validate, etc.
        blt validate

        # run phpunit tests
        blt tests:phpunit

        # ssh into vm & run behat tests
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

## Drush aliases

It's recommended to install Drush aliases in your repository that all developers can use to access your remote sites. If you are using Acquia Cloud, follow the instructions on [Acquia Cloud](https://docs.acquia.com/acquia-cloud/drush/aliases) or use [Club](https://github.com/acquia/club#usage) to download your aliases and place them in the `drush/site-aliases` directory.
