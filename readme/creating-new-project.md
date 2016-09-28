# Creating a new project with BLT

1. Create a new project using the [blt-project](https://github.com/acquia/blt-project) template:

        composer clear-cache
        export COMPOSER_PROCESS_TIMEOUT=2000
        composer create-project acquia/blt-project MY_PROJECT --no-interaction
        cd MY_PROJECT

1. Install the `blt` alias and follow on-screen instructions:

        composer blt-alias

1. Customize `project.yml`.
1. Create & boot the VM, install Drupal. 

        blt vm
        blt local:setup

1. Login to Drupal `drush @[project.machine_name].local uli`, where [project.machine_name] is the value that you set in project.yml.

## Next Steps

Now that your new project works locally, review [BLT documentation by role](https://http://blt.readthedocs.io/) to learn how to perform common project tasks and integrate with third party tools.

A few popular commands:

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
