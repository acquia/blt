# ACSF Setup

To configure a project to run on ACSF, perform the following steps after initially setting up BLT:

1. Add the following line to project.yml: `hosting: "acsf"`. This ensures that the correct settings files are added to the deployment artifact.
1. Execute `./blt.sh acsf:init` from the project root.
1. Ensure that `drupal/acsf` is a listed dependency in your composer.json file: `composer require drupal/acsf:~8`
1. Add the acsf module as a dependency to your installation profile

## Factory Hooks and settings.php

Acquia Cloud Site Factory does not allow changes to `settings.php`. In order to make additions normally done with `settings.php`, factory hooks are used. Please see the [documentation](https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks) for reference.
