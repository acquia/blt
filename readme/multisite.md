# Multisite

This document will walk you through the steps to set up a multisite BLT-based project.

## Acquia Cloud setup

Start by following the [Acquia Cloud multisite instructions](https://docs.acquia.com/acquia-cloud/multisite) to configure your codebase for Acquia Cloud. Specifically, these instructions should walk you through:

1. Creating a new database in Cloud.
2. Creating a new site directory in your codebase. It's recommended to name each directory according to the site's primary domain (e.g. docroot/sites/example.com).
3. Creating a sites.php file to direct incoming HTTP requests to the appropriate site. Note that if you name your sites according to their domain names, and use a canonical approach to subdomains (local.example.com, dev.example.com, example.com), you don't need to modify sites.php at all--but the file does need to exist, even if it's empty.
4. Adding the site-specific settings include to each site's settings.php file. In the `settings.php` for your multisite, add the `require` statement for your multisite database credentials *before* the `require` statement for `blt.settings.php`. E.g.,

        if (file_exists('/var/www/site-php')) {
          require '/var/www/site-php/mysite/multisitename-settings.inc';
        }
        
        require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/blt.settings.php";

## BLT setup

Start by setting `$site_dir` in each site's settings.php, prior to the `blt.settings.php` include. This is necessary for BLT to set a number of configurations correctly, such as your public and private file paths:

    $site_dir = 'example.com';

Ensure that your new project has `$settings['install_profile']` set, or Drupal core will attempt (unsuccessfully) to write it to disk!

At this point you should have a functional multisite codebase that can be installed on Acquia Cloud.

## Drush aliases

The default Drush site aliases provided by [Acquia Cloud](https://docs.acquia.com/acquia-cloud/drush/aliases) and [Club](https://github.com/acquia/club#usage) are not currently multisite-aware. They will connect to the first ("default") site / database on the subscription by default. You will need to create your own Drush aliases for each site.

It's recommended to copy the aliases file provided by Acquia Cloud or Club to create a separate aliases file for each site. Simply modify the `uri` and `parent` keys for the aliases within each file to match the correct database / site.

TODO: Add instructions for integration with BLT development workflows and DrupalVM.
