# Creating a new project with BLT

*Please do not clone BLT or BLT Project as a means of using them. Just follow the directions below.*

1. Create a new project with the commands below. It will create the necessary directories and download all dependencies. **Do not run these commands inside of an existing git repository**. Replace `MY_PROJECT` in the commands below with the desired name of your new project directory.

        composer create-project --no-interaction acquia/blt-project my-project
        cd my-project

1. If this is your first time using BLT on this machine, restart your shell so that Bash detects the new BLT alias.
1. Customize `blt/project.yml`.

    By default, BLT will install sites using the [`lightning`](https://github.com/acquia/lightning) profile, other valid values are `standard` or `minimal`.

    If you want to use another contributed profile (such as Headless Lightning), now is the time to download that and change the corresponding key in `blt/project.yml`:

         composer require acquia/headless-lightning:~1.1.0

1. Now it’s time to spin up your LAMP stack.

    1. **Recommended**: Run the following command to create a DrupalVM instance:

             blt vm

       Optional: to customize your VM (such as to enable Solr or change the PHP version), respond 'no' when BLT offers to boot your VM, and [make any necessary modifications](http://docs.drupalvm.com/en/latest/getting-started/configure-drupalvm/) to `box/config.yml` before starting your VM.

    1. **Alternative**: To set up your own LAMP stack, please review [Local Development](http://blt.readthedocs.io/en/8.x/readme/local-development/), then execute the following command to generate default local settings files:

             blt setup:settings

       Modify the generated `docroot/sites/default/settings/local.settings.php` file by adding your custom MySql credentials.

1. Execute the following command to complete setup:

        blt setup

    This will generate all required files and install Drupal.

1. Login to Drupal: `drush @my-project.local uli` (replace `my-project` with the name of your project, which should be the value of [project.machine_name] in `blt/project.yml`).

1. See [Next steps](next-steps.md).

## Troubleshooting

If you have trouble creating the project, try clearing the Composer cache or increasing the process timeout:

        composer clear-cache
        export COMPOSER_PROCESS_TIMEOUT=2000

If you have trouble using the `blt` alias, make sure it’s installed correctly and then restart your terminal session:

        composer run-script blt-alias
        source ~/.bash_profile

If you get syntax errors from vendor packages, check that the version of PHP on your host matches the version of PHP in your VM, or else make sure to always run composer commands from within the VM.
