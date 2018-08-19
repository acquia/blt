# Multisite

There are two parts to setting up a multisite instance on BLT: the local setup and the cloud setup.

## Local setup

1. Set up a single site on BLT, following the standard instructions, and ssh to the vm (`vagrant ssh`).
1. Run `blt recipes:multisite:init`.

    Running `blt recipes:multisite:init`...

    * Sets up new a directory in your docroot/sites directory with the multisite name given with all the necessary files and subdirectories.
    * Sets up a new drush alias.
    * Sets up a new vhost in the box/config.yml file.
    * Grants necessary permissions to the MySQL user in the box/config.yml file.
    * Generates sites.php file.
    * Updates the new site's database credentials.
    * Updates the new site's local drush configuration.

1. If desired override any blt settings in the `docroot/sites/{newsite}/blt.yml` file.
1. Once you've completed the above and any relevant manual steps, exit out of your virtual machine environment and update with the new configuration using `vagrant provision`.


#### Add a multisite array to `blt/blt.yml`

You have the option to define your multisites in `blt/blt.yml` by creating a `multisites` array. This allows BLT to run setup and deployment tasks for each site in the codebase. If you don't manually define this variable, BLT will automatically set it based on discovered multisite directories.

    multisites:
      - default
      - example.com

Ensure that your new project has `$settings['install_profile']` set, or Drupal core will attempt (unsuccessfully) to write it to disk!

At this point you should have a functional multisite codebase that can be installed on Acquia Cloud.

#### Set up a sites.php file.

BLT creates a sites.php file in `docroot/sites/` to allow your Drupal instance to direct incoming HTTP requests to the appropriate site.

This file must exist to install Drupal on a multisite in Drush 9. Drupal core provides an `example.sites.php` file which can be copied, renamed, and modified as needed. When running the `blt init:settings` task, BLT maps your local multisite canonical domains to their respective site directory in `docroot/sites/[sitename]`. If this file is blank or the `$sites[]` array does not map to a valid directory, then Drush and BLT will use the values in the default site at `docroot/sites/default`. This will likely also cause issues with multisite drush aliases using the incorrect site uri and database credentials.


#### Override BLT variables in `docroot/sites/{newsite}/blt.yml`

You may override BLT variables on a per-site basis by editing the `blt.yml` file in `docroot/sites/{newsite}/`. You may then run BLT with the `site` variable set at the command line to load the site's properties.

For instance, the `drush` aliases for your site in `docroot/sites/mysite` were `@mysite.local` and `@mysite.test`, you could define these in `docroot/sites/mysite/blt.yml` as:

```yaml
drush:
  aliases:
    local: self
    remote: mysite.test
```

Then, to refresh your local site, you could run: `blt drupal:sync --site=mysite`.

## Acquia Cloud setup

Start by following the [Acquia Cloud multisite instructions](https://docs.acquia.com/acquia-cloud/multisite) to configure your codebase for Acquia Cloud. Specifically, these instructions should walk you through:

1. Creating a new database in Cloud.
2. Adding the site-specific settings include to each site's settings.php file. In the `settings.php` for your multisite, add the `require` statement for your multisite database credentials *before* the `require` statement for `blt.settings.php`, e.g.,

        if (file_exists('/var/www/site-php')) {
          require '/var/www/site-php/mysite/multisitename-settings.inc';
        }

        require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/blt.settings.php";

### Drush aliases

The default Drush site aliases provided by [Acquia Cloud](https://docs.acquia.com/acquia-cloud/drush/aliases) are not currently multisite-aware. They will connect to the first ("default") site / database on the subscription by default. You will need to create your own Drush aliases for each site. It's recommended to copy the alias file provided by the `blt aliases` command for each Acquia CLoud multisite into a separate alias file for each site. Simply modify the `uri` and `parent` keys for the aliases within each file to match the correct database / site to the Acquia Cloud environment.

*Note that the aliases downloaded from Acquia Cloud through the link on your user's Profile > Credenditials > Drush integration page or through the Drush 8 `drush acquia-update` command are not supported on Acquia Cloud Site Factory subscriptions* BLT currently generates drush aliases for each of your Acquia Cloud Site Factory sites with the `blt aliases` command.


