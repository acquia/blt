# Creating a new project with BLT

*Please do not clone BLT or BLT Project as a means of using them. Just follow the directions below.*

1. Create a new project with the commands below. It will create the necessary directories and download all dependencies. **Do not run these commands inside of an existing git repository**. Replace `MY_PROJECT` in the commands below with the desired name of your new project directory.

        composer clear-cache
        export COMPOSER_PROCESS_TIMEOUT=2000
        composer create-project --no-interaction acquia/blt-project MY_PROJECT
        cd MY_PROJECT

1. Install the `blt` alias and follow on-screen instructions:

        composer run-script blt-alias
        source ~/.bash_profile

1. Customize `blt/project.yml`.

    By default, BLT will use the [`lightning`](https://github.com/acquia/lightning) profile, other valid values are `standard` or `minimal`.

1. If using Drupal VM for local development, run the following commands. Otherwise, see [Local Development](http://blt.readthedocs.io/en/8.x/readme/local-development/).

        blt vm
        blt local:setup

1. Login to Drupal `drush @[project.machine_name].local uli`, where [project.machine_name] is the value that you set in project.yml.
1. See [Next steps](next-steps.md).
