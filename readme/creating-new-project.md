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

1. Now its time to spin up your LAMP stack.

    1. **With Drupal VM**: If you would like to use Drupal VM for local development, run the following commands:

             blt vm

    1. **Without Drupal VM**: If you would not like to use Drupal VM, please review [Local Development](http://blt.readthedocs.io/en/8.x/readme/local-development/) and set up your own LAMP stack. Once your LAMP stack is running, execute the following command to generate default local settings files:

             blt setup:settings

       Modify the generated `docroot/sites/default/settings/local.settings.php` file by adding your custom MySql credentials.

1. Execute the following command to complete setup:

        blt setup

    This will generate all required files and install Drupal.

1. Login to Drupal `drush @[project.machine_name].local uli`, where [project.machine_name] is the value that you set in project.yml.

1. See [Next steps](next-steps.md).
