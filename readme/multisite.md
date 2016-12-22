# Multisite

## Acquia Cloud settings

In the `settings.php` for your multisite, add the `require` statement for your multisite database credentials *before* the `require` statement for `blt.settings.php`. E.g.,

        if (file_exists('/var/www/site-php')) {
          require '/var/www/site-php/mysite/multisitename-settings.inc';
        }

        require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/blt.settings.php";

Ensure that your new project has `$settings['install_profile']` set, or Drupal core will attempt (unsuccessfully) to write it to disk!
