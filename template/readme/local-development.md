# Local Development

Acquia currently recommends the use of either:

  * [Drupal VM](#drupal-vm): An isolated virtual machine, built with Vagrant and Ansible.
  * [Acquia Dev Desktop](#dd): A turn-key LAMP stack tailored specifically for Acquia-hosted Drupal sites.

No matter what local environment you choose to use, the following guidelines should be followed:

  * In order to guarantee similar behavior, use Apache as your web server.
  * If your project is hosted on Acquia Cloud, please ensure to match [our software versions](https://docs.acquia.com/cloud/arch/tech-platform).

Acquia developers use [PHPStorm](http://www.jetbrains.com/phpstorm/) and recommend it for local development environments. Acquia has written [several articles](https://docs.acquia.com/search/site/phpstorm) on effectively using PHPStorm for Drupal development.

### Operating Systems

We highly recommend that you *do not use Windows* directly for development. Many development tools (e.g., drush, gulp, etc.) are not built or tested for Windows compatibility. Furthermore, most CI solutions (e.g., Travis CI, Drupal CI, etc.) do not permit testing on Windows OS.

If you must use Windows, we recommend that:
* You have administrator access to your machine
* You execute the necessary command line functions a bash emulator such as:
    * [Git Bash](https://git-for-windows.github.io/)
    * [cmder](http://cmder.net/)
    * [cygwin](https://www.cygwin.com/)

## <a name="drupal-vm"></a>Using Drupal VM for BLT-generated projects

_BLT support for Drupal VM is experimental. Not all BLT features currently work with Drupal VM. Additionally, Drupal VM integration with BLT cannot be tested via Travis CI, and is prone to regressions._

To use [Drupal VM](http://www.drupalvm.com/) with a Drupal project that is generated with BLT:

1. Execute `./blt.sh vm:init` from the project root directory.
1. Follow the Quick Start Guide in [Drupal VM's README](https://github.com/geerlingguy/drupal-vm#quick-start-guide)

There are also other changes you can make if you choose to match the Acquia Cloud server configuration more closely. See Drupal VM's example configuration changes in Drupal VM's `examples/acquia/acquia.overrides.yml` file.

Once you've made these changes and completed the steps in Drupal VM's Quick Start Guide, you may run `vagrant up` to bring up your local development environment, and then access the site via the configured `drupal_domain`.

### Drupal VM and Behat tests

#### Using the Drupal Extension's "drupal" driver with Drupal VM

The Drupal Extension for Behat has an [inherent limitation](https://behat-drupal-extension.readthedocs.io/en/3.1/drivers.html): it cannot use the 'drupal' driver to bootstrap Drupal on a remote server. If you're using Drupal VM and would like to execute Behat tests using the 'drupal' driver, you must execute them from within the VM. This is a break of the established pattern of running all BLT commands outside of the VM.

To execute Behat tests using the 'drupal' driver on a Drupal VM environment, you must do the following:

1. Update `tests/behat/local.yml` with the absolute file path to your project _inside the VM_. I.e., find and replace all instances of `host/machine/path/to/repo` with `/vm/path/to/repo`, which should look something like `var/www/[project.machine_name]`. 
1. SSH into the VM `vagrant ssh`.
1. Change to your project directory `cd /var/www/[project.machine_name]`.
1. Assert that PhantomJS is installed for VM: `composer run-script install-phantomjs`
1. Execute behat tests `./blt.sh tests:behat`

#### Using the Drupal Extension's "drush" driver with Drupal VM

You may choose to write only behat tests that utilize the Drupal Extension's "drupal" driver. Doing this will allow you to run `./blt.sh tests:behat` from the host machine without modificaitons to the Behat local.yml configuration.

## <a name="dd"></a>Using Acquia Dev Desktop for BLT-generated projects

### Project creation and installation changes

Add a new site in [Dev Desktop](https://www.acquia.com/products-services/dev-desktop) by selecting _Import local Drupal site_. Point it at the `docroot` folder inside your new code base. Your `/sites/default/settings.php` file will be modified automatically to include the Dev Desktop database connection information.

### Drush support

In order to use a custom version of Drush (required by BLT) with Dev Desktop, you must:

1. Add the following lines to `~/.bash_profile`:

  ```
  export PATH="/Applications/DevDesktop/mysql/bin:$PATH"
  export DEVDESKTOP_DRUPAL_SETTINGS_DIR="~/.acquia/DevDesktop/DrupalSettings"
  
  ```
1. Ensure that Dev Desktop's PHP binary is being used on the CLI. You can check this via `which php`. 
1. Enable the usage of environmental variables by adding the following line to `php.ini`, which you can locate with `php --ini`:

  ```
  variables_order = "EGPCS"
  ```
1. Restart your terminal session after making the aforementioned changes.

## Alternative local development environments

For reasons, some teams may prefer to use a different development environment. Drupal VM offers a great deal of flexibility and a uniform configuration for everyone, but sometimes a tool like Acquia Dev Desktop, MAMP/XAMPP, or a different environment (e.g. a bespoke Docker-based dev environment) may be preferable.

It is up to each team to choose how to handle local development, but some of the main things that help a project's velocity with regard to local development include:

  - Uniformity and the same configuration across all developer environments.
  - Ease of initial environment configuration (if it takes more than an hour to get a new developer running your project locally, you're doing it wrong).
  - Ability to emulate all aspects of the production environment with minimal hassle (e.g. Varnish, Memcached, Solr, Elasticsearch, different PHP versions, etc.).
  - Helpful built-in developer tools (e.g. XHProf, Xdebug, Adminer, PimpMyLog).
  - Ease of use across Windows, Mac, or Linux workstations.

If you choose to use a different solution than recommended here, please make sure it fits all the needs of your team and project, and will not be a hindrance to project development velocity!
