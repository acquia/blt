# Creating a new project with BLT

*Do not clone BLT or BLT Project as a means of using them. Just follow the directions below.*

1. Pick a machine name for your new project, e.g., *my-project*. For compatibility with third-party tools, it's best to only use letters, numbers, and hyphens.

1. Run the following command to create your new project and download all dependencies (including BLT).

        composer create-project --no-interaction acquia/blt-project my-project

    All subsequent steps assume you are in the project directory (`cd my-project`).

1. If this is your first time using BLT on this machine, restart your shell so that Bash detects the new BLT alias.

1. Customize *blt/blt.yml* if desired, such as to choose an install profile.

    By default, BLT will install sites using the [*lightning*](https://github.com/acquia/lightning) profile. You can change this to any other core, contributed, or custom profile in your codebase. Make sure to download the profile if necessary, e.g., `composer require acquia/headless_lightning:~1.1.0`.

1. Now it’s time to spin up your LAMP stack.

    1. **Recommended**: Run the following command to create a DrupalVM instance:

             blt vm

       To customize your VM (such as to enable Solr or change the PHP version), respond *no* when BLT offers to boot your VM, and [make any necessary modifications](http://docs.drupalvm.com/en/latest/getting-started/configure-drupalvm/) to *box/config.yml* before starting your VM.

    1. **Alternative**: To set up your own LAMP stack, please review [Local Development](http://blt.readthedocs.io/en/9.x/readme/local-development/), then execute the following command to generate default local settings files:

             blt blt:init:settings

       Modify the generated *docroot/sites/default/settings/local.settings.php* file by adding your custom MySql credentials.

1. Install Drupal and automatically generate any remaining required files (e.g., settings.php, hash salt, etc...):

        blt setup

1. Log in to Drupal: `drush @my-project.local uli` (replace *my-project* with the name of your project, which should be the value of *project.machine_name* in *blt/blt.yml*).

1. Congratulations, you now have a running local Drupal site using BLT! See [Next steps](next-steps.md).

## Troubleshooting

If you have trouble creating the project, try clearing the Composer cache or increasing the process timeout:

        composer clear-cache
        export COMPOSER_PROCESS_TIMEOUT=2000

If you have trouble using the `blt` alias, make sure it’s installed correctly and then restart your terminal session:

        composer run-script blt-alias
        source ~/.bash_profile

If you get syntax errors from vendor packages, check that the version of PHP on your host matches the version of PHP in your VM, or else make sure to always run composer commands from within the VM.

Finally, if you continue to have trouble, review the remaining [BLT documentation](http://blt.readthedocs.io/en/latest/), search for [relevant issues](https://github.com/acquia/blt/issues), or [create a new issue](https://github.com/acquia/blt/issues/new).
