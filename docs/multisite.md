# Multisite

There are two parts to setting up a multisite instance on BLT: the local setup and the cloud setup.

## Local setup

1. Set up a single site on BLT, following the standard instructions, and ssh to the vm (`vagrant ssh`).
1. Run `blt recipes:multisite:init`. It is suggested to use a simple machine name (rather than a domain name) for your site for [maximum compatibility with other BLT features](https://github.com/acquia/blt/pull/3503#issuecomment-477416463).

    Running `blt recipes:multisite:init`...

    * Sets up new a directory in your docroot/sites directory with the multisite name given with all the necessary files and subdirectories.
    * Sets up a new drush alias.
    * Sets up a new vhost in the box/config.yml file.
    * Sets up a new MySQL user in the box/config.yml file.
    
    Some manual configuration is still required via the following steps.

1. Set the new site's local database credentials in the `docroot/sites/{newsite}/settings/local.settings.php` file to ensure your new site connects to the correct database.
1. Copy core's `example.sites.php` file, rename it to `sites.php`, and add entries for your new site.
1. If desired override any blt settings in the `docroot/sites/{newsite}/blt.yml` file.
1. Once you've completed the above and any relevant manual steps, exit out of your virtual machine environment and update with the new configuration using `vagrant provision`.

### Optional local setup steps

#### Add a multisite array to `blt/blt.yml`

You have the option to explicitly define your multisites in `blt/blt.yml` by creating a `multisites` array. If you don't manually define this variable, BLT will automatically set it based on discovered multisite directories.

    multisites:
      - default
      - example

At this point you should have a functional multisite codebase that can be installed on Acquia Cloud.

#### Override BLT variables in `docroot/sites/{newsite}/blt.yml`

You may override BLT variables on a per-site basis by editing the `blt.yml` file in `docroot/sites/{newsite}/`. You may then run BLT with the `site` variable set at the command line to load the site's properties.

For instance, if the `drush` aliases for your site in `docroot/sites/mysite` were `@mysite.local` and `@mysite.test`, you could define these in `docroot/sites/mysite/blt.yml` as:

```yaml
drush:
  aliases:
    local: mysite.local
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

The default Drush site aliases provided by [Acquia Cloud](https://docs.acquia.com/acquia-cloud/drush/aliases) and [Club](https://github.com/acquia/club#usage) are not currently multisite-aware. They will connect to the first ("default") site / database on the subscription by default. You will need to create your own Drush aliases for each site.

It's recommended to copy the aliases file provided by Acquia Cloud or Club to create a separate aliases file for each site. Simply modify the `uri` and `parent` keys for the aliases within each file to match the correct database / site.
