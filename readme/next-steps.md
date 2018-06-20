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

It's recommended to install Drush aliases in your repository that all developers can use to access your remote sites. If you are using Acquia Cloud, run `blt recipes:aliases:init:acquia` to generate your aliases and place them in the `drush/sites` directory.
