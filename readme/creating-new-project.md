# Creating a new project with BLT

1. Create a new project using the [blt-project](https://github.com/acquia/blt-project) template:

        composer clear-cache
        export COMPOSER_PROCESS_TIMEOUT=2000
        composer create-project acquia/blt-project MY_PROJECT --no-interaction
        cd MY_PROJECT

1. Install the `blt` alias and follow on-screen instructions:

        composer blt-alias

1. Customize `project.yml`.
    
    By default, BLT will use the [`lightning`](https://github.com/acquia/lightning) profile, other valid values are `standard` or `minimal`.

1. If using Drupal VM for local development, run the following commands. Otherwise, see [Local Development](http://blt.readthedocs.io/en/8.x/readme/local-development/).

        blt vm
        blt local:setup

1. Login to Drupal `drush @[project.machine_name].local uli`, where [project.machine_name] is the value that you set in project.yml.
1. See [Next steps](next-steps.md).
