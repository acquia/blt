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

To use [Drupal VM](https://www.acquia.com/products-services/dev-desktop) with a Drupal project that is generated with BLT:

1. Place your downloaded copy of Drupal VM inside the generated Drupal project folder, and name the drupal-vm directory `box`.
1. Inside the new project's `build/custom/phing/build.yml` file, override the `docroot` used for Drush commands with the value:

    ```
    drush:
      root: ''
    ```
1. Add a drush alias to `drush/site-aliases/aliases.drushrc.php`:

    ```
    // [vagrant_machine_name].local
    $aliases['[vagrant_machine_name].local'] = array(
      // /var/www/[vagrant_machine_name]/docroot
      'root' => '/var/www/[vagrant_machine_name]/docroot',
      // vagrant_hostname
      'uri' => '[vagrant_hostname]',
      // vagrant_hostname
      'remote-host' => '[vagrant_hostname]',
      'remote-user' => 'vagrant',
      'ssh-options' => '-i ~/.vagrant.d/insecure_private_key',
    );
    ```
1. Updating `project.yml` so that `drush.default_alias` is set to your Drupal VM alias.
1. Follow the Quick Start Guide in [Drupal VM's README](https://github.com/geerlingguy/drupal-vm#quick-start-guide)
1. Before you run `vagrant up`, make the following changes to your VM `config.yml` file:

    ```
    # Update the hostname to the local development environment hostname.
    vagrant_hostname: [project_local_domain]
    vagrant_machine_name: [project_machine_name]

    # Provide the path to the project root to Vagrant.
    vagrant_synced_folders:
      # Set the local_path for the first synced folder to `../`.
      - local_path: ../
        # Set the destination to the Acquia Cloud subscription machine name.
        destination: /var/www/[project_machine_name]
        type: nfs

    # Set this to `7` for a Drupal 7 site, or `8` for a Drupal 8 site.
    drupal_major_version: 8

    # Set drupal_core_path to the `destination` in the synced folder
    # configuration above, plus `/docroot`.
    drupal_core_path: /var/www/[project_machine_name]/docroot

    # Set drupal_domain to the same thing as the `vagrant_hostname` above.
    drupal_domain: [project_local_domain]

    # Set drupal_site_name to the project's human-readable name.
    drupal_site_name: [project_human_name]

    # If you build the makefile using BLT's built-in Phing task (recommended),
    # set `build_makefile` to `false`.
    build_makefile: false

    # If you need to install the site inside the VM, set `install_site` to
    # `true`. Otherwise, after you build the VM, you can import the database
    # using Drush, Adminer, or any other method of connecting to the MySQL
    # database.
    install_site: true

    # To add support for XSL, which is used for some BLT-supplied tests, add
    # `php5-xsl` to `extra_packages`.
    extra_packages:
      - unzip
    # - php5-xsl

    drupal_mysql_user: drupal
    drupal_mysql_password: drupal
    ```


There are also other changes you can make if you choose to match the Acquia Cloud server configuration more closely. See Drupal VM's example configuration changes in Drupal VM's `examples/acquia/acquia.overrides.yml` file.

Once you've made these changes and completed the steps in Drupal VM's Quick Start Guide, you may run `vagrant up` to bring up your local development environment, and then access the site via the configured `drupal_domain`.

## <a name="dd"></a>Using Acquia Dev Desktop for BLT-generated projects

### Project creation and installation changes

Add a new site in [Dev Desktop](https://www.acquia.com/products-services/dev-desktop) by selecting _Import local Drupal site_. Point it at the `docroot` folder inside your new code base. Your `/sites/default/settings.php` file will be modified automatically to include the Dev Desktop database connection information.

### Drush support

In order to use a custom version of Drush with Dev Desktop, you must add the
following lines to ~/.bash_profile:

```
export PATH="/Applications/DevDesktop/mysql/bin:$PATH"
export DEVDESKTOP_DRUPAL_SETTINGS_DIR="$HOME/.acquia/DevDesktop/DrupalSettings"

```

Restart your terminal session after adding these lines.

## Alternative local development environments

For reasons, some teams may prefer to use a different development environment. Drupal VM offers a great deal of flexibility and a uniform configuration for everyone, but sometimes a tool like Acquia Dev Desktop, MAMP/XAMPP, or a different environment (e.g. a bespoke Docker-based dev environment) may be preferable.

It is up to each team to choose how to handle local development, but some of the main things that help a project's velocity with regard to local development include:

  - Uniformity and the same configuration across all developer environments.
  - Ease of initial environment configuration (if it takes more than an hour to get a new developer running your project locally, you're doing it wrong).
  - Ability to emulate all aspects of the production environment with minimal hassle (e.g. Varnish, Memcached, Solr, Elasticsearch, different PHP versions, etc.).
  - Helpful built-in developer tools (e.g. XHProf, Xdebug, Adminer, PimpMyLog).
  - Ease of use across Windows, Mac, or Linux workstations.

If you choose to use a different solution than recommended here, please make sure it fits all the needs of your team and project, and will not be a hindrance to project development velocity!
