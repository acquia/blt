# Setting up BLT with Docksal

By default, BLT is set to work with Drupal VM. It is easy to set up to use with Docksal, but
you will have to install the Docksal `blt` addon command and make some configuration changes.

## Setting Your Project to Use Docksal

If you already have Docksal installed on your system, setting your BLT project to use Docksal only
requires that you add a `.docksal` directory in your project and run `fin up` in the command prompt
of your project directory. For sites hosted on Acquia, you may want to set the stack to Acquia by running 
`fin config set DOCKSAL_STACK=acquia`, which will set up the project with varnish, memcache, and solr as well. See the [Docksal Documentation](https://docs.docksal.io) for more specific
Docksal information.

## Using the Docksal `blt` Command

BLT will install a `blt alias` command on your host system. Running the `blt` command will
then use the resources of your host system. Instead, you need to make sure you're running
the commands through the Docksal CLI service.

Install the Docksal `blt` addon command:
```
fin addon install blt
```

Run any blt command with `fin` and you will be running the command within the Docksal CLI, e.g.:
```
fin blt doctor
```


## Setting the Database Connection and Drush Alias

### New Project Setup

The default Docksal database settings are different than the BLT defaults. If you have not already
added special settings to the local.settings.php file that BLT creates, it is best to begin by
following these steps:

1. remove the `docroot/sites/default/settings/local.settings.php` and the `docroot/sites/default/local.drush.yml` files
2. update the blt.yml file in your project's blt directory:
    ```yaml
    project:
      machine_name: myproject
      local:
        hostname: '${env.VIRTUAL_HOST}'
    drupal:
      db:
        database: default
        username: user
        password: user
        host: db
        port: 3306
    ```
3. initiate and verify blt settings
    ```bash
    fin blt blt:init:settings
    ```
4. setup your Drupal site
    ```bash
    fin blt setup -D setup.strategy=install
    ```

    Note: By default, passing the strategy option here is not required since BLT defaults to the install strategy. 
    Alternatively, you may use a sync setup strategy if there is an existing remote with a source database to used in a Drush sql:sync operation:
    ```bash
    fin blt setup -D setup.strategy=sync
    ```

### Existing project setup
If you have an existing BLT project with Drupal install, you may prefer to configure your `local.settings.php`
file and your `local.drush.yml` file. It is best practice to also update your blt.yml as outlined above, but
it is not necessary to delete these files.

1. configure your `docroot/sites/default/settings/local.settings.php` file with these variable values:

    ```php
    $db_name = 'default';

    $databases = array(
      'default' =>
      array(
        'default' =>
        array(
          'database' => $db_name,
          'username' => 'user',
          'password' => 'user',
          'host' => 'db',
          'port' => '3306',
          'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
          'driver' => 'mysql',
          'prefix' => '',
        ),
      ),
    );
    ```
2. configure your `docroot/sites/default/local.drush.yml` file:

    ```yaml
    options:
      uri: 'http://myproject.docksal'
    ```
3. Assuming your site's drush remote is @sitegroup.siteid then you can update your BLT configuration (either at the project level or site level):

    At the project level (blt/blt.yml) or site level (e.g., sites/default/blt.yml):
    ```yaml
    setup:
      strategy: sync

    drush:
      aliases:
        remote: sitegroup.siteid
    ```
So when setting up the project for the first time when running fin blt setup it will execute the sync setup strategy rather than the install strategy
(sql:sync vs site:install), effectively executing
`drush sql:sync @sitegroup.siteid @self`.

For more, read the [extended blog post](https://blog.docksal.io/docksal-and-acquia-blt-1552540a3b9f) with a walkthrough to set up a new BLT project with Docksal.

## Settings for a Docksal-enabled CI Environment

To continuously verify a profile installs successfully and existing sites also synchronize correctly, set 
two different environment-specific blt.yml files in the default site directory (`install.blt.yml` and `sync.blt.yml`). 
Each of those can contain different behat/setup configuration for installing/syncing sites and then running different sets 
of Behat tests on each. You can then have a Docksal command `fin test-environment` which accepts an argument meant to be the 
environment indicator. That command in turn can run `fin blt setup --environment=$1` && `fin blt tests --environment=$1`, which 
will setup the site according to configuration as well as run tests against that site according to the same set of 
configuration.
