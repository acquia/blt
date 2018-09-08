
# Local Development

Acquia currently recommends the use of either:

  * [Drupal VM](#using-drupal-vm-for-blt-generated-projects): an isolated virtual machine, built with Vagrant and Ansible
  * [Acquia Dev Desktop](#using-acquia-dev-desktop-for-blt-generated-projects): a turn-key LAMP stack tailored specifically for Acquia-hosted Drupal sites
  * [Alternative local development environments](#alternative-local-development-environments)

No matter what local environment you choose to use, the following guidelines should be followed:

  * In order to guarantee similar behavior, use Apache as your web server
  * If your project is hosted on Acquia Cloud, please ensure to match [our software versions](https://docs.acquia.com/cloud/arch/tech-platform)

Acquia developers use [PHPStorm](http://www.jetbrains.com/phpstorm/) and recommend it for local development environments. Acquia has written [several articles](https://docs.acquia.com/#stq=phpstorm&stp=1) on effectively using PHPStorm for Drupal development.

## Using Drupal VM for BLT-generated projects

To use [Drupal VM](http://www.drupalvm.com/) with a Drupal project that is generated with BLT:

1. Download the Drupal VM dependencies listed in [Drupal VM's README](https://github.com/geerlingguy/drupal-vm#quick-start-guide). If you're running [Homebrew](http://brew.sh/index.html) on Mac OSX, this is as simple as:

        brew tap caskroom/cask
        brew install php56 git composer ansible drush
        brew cask install virtualbox vagrant

1. Create & boot the VM

        blt vm
        
1. Commit all resulting files, including `box/config.yml` and `Vagrantfile`.
        
        git add -A
        git commit -m <your commit meessage>

1. Install Drupal

        vagrant ssh
        blt setup

1. Login to Drupal

        drush uli

There are also other changes you can make if you choose to match the Acquia Cloud server configuration more closely. See Drupal VM's example configuration changes in Drupal VM's `examples/acquia/acquia.overrides.yml` file.

Subsequently, you should use `vagrant` commands to interact with the VM. Do not re-run `blt vm`. For instance, use `vagrant up` to start the VM, and `vagrant halt` to stop it.

Note: With a Drupal VM setup, BLT expects all commands (with the exception of commands in the `blt vm` namespace), to be executed within the VM. To SSH into the VM, simply run `vagrant ssh` as you did in the "Install Drupal" step above.

### Drupal VM and Behat tests

#### Using the Drupal Extension's "drupal" driver with Drupal VM

The Drupal Extension for Behat has an [inherent limitation](https://behat-drupal-extension.readthedocs.io/en/3.1/drivers.html): it cannot use the 'drupal' driver to bootstrap Drupal on a remote server. If you're using Drupal VM and would like to execute Behat tests using the 'drupal' driver, you must execute them from within the VM. This is a break of the established pattern of running all BLT commands outside of the VM.

To execute Behat tests using the 'drupal' driver on a Drupal VM environment, you must do the following:

1. SSH into the VM `vagrant ssh`
1. Execute behat tests `blt tests:behat:run`

Alternatively, you may choose to write only behat tests that utilize the Drupal Extension's "drush" driver. Doing this will allow you to run `blt tests:behat:run` from the host machine.

## Using Acquia Dev Desktop for BLT-generated projects

### Project creation and installation changes

1. Add a new site in [Dev Desktop](https://www.acquia.com/products-services/dev-desktop) by selecting _Import local Drupal site_. Point it at the `docroot` folder inside your new code base. Your `/sites/default/settings.php` file will be modified automatically to include the Dev Desktop database connection information.
1. Follow the normal setup process by executing `blt setup`.

### Drush support

In order to use a custom version of Drush (required by BLT) with Dev Desktop, you must:

1. Add the following lines to `~/.bash_profile` (or equivalent file):

        export PATH="/Applications/DevDesktop/mysql/bin:$PATH"
        export DEVDESKTOP_DRUPAL_SETTINGS_DIR="$HOME/.acquia/DevDesktop/DrupalSettings"

1. Ensure that Dev Desktop's PHP binary is being used on the CLI. This will require adding a line *like* this to your `~/.bash_profile`:

        export PATH=/Applications/DevDesktop/php7_0/bin:$PATH

    The exact line will depend upon the version of PHP that you intend to use. You can check the effect of this value via `which php`.

1. Enable the usage of environmental variables by adding the following line to `php.ini`, which you can locate with `php --ini`:

        variables_order = "EGPCS"

1. Restart your terminal session after making the aforementioned changes.
1. Optionally, run `blt doctor` to verify your configuration.

## Alternative local development environments

Some teams may prefer to use a different development environment. Drupal VM offers a great deal of flexibility and a uniform configuration for everyone, but sometimes a tool like Acquia Dev Desktop, MAMP/XAMPP, or a different environment (e.g., a bespoke Docker-based dev environment) may be preferable.

It is up to each team to choose how to handle local development, but some of the main things that help a project's velocity with regard to local development include:

* Uniformity and the same configuration across all developer environments
* Ease of initial environment configuration (if it takes more than an hour to get a new developer running your project locally, you're doing it wrong)
* Ability to emulate all aspects of the production environment with minimal hassle (e.g., Varnish, Memcached, Solr, Elasticsearch, different PHP versions, etc.)
* Helpful built-in developer tools (e.g., XHProf, Xdebug, Adminer, PimpMyLog)
* Ease of use across Windows, Mac, or Linux workstations

If you choose to use a different solution than recommended here, please make sure it fits all the needs of your team and project, and will not be a hindrance to project development velocity!

While the BLT team cannot officially support these alternative environments, please submit documentation of any special steps necessary with the tool you choose so that others can learn from your experience. Environments for which others have contributed tips include:
- [Lando](alternative-environment-tips/lando.md)
