<?php

/**
 * @file
 * Local development override configuration feature.
 */

use Drupal\Component\Assertion\Handle;

$db_name = '${drupal.db.database}';
if (isset($acsf_site_name)) {
  $db_name .= '_' . $acsf_site_name;
}

/**
 * Database configuration.
 */
$databases = array(
  'default' =>
  array(
    'default' =>
    array(
      'database' => $db_name,
      'username' => '${drupal.db.username}',
      'password' => '${drupal.db.password}',
      'host' => '${drupal.db.host}',
      'port' => '${drupal.db.port}',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
      'driver' => 'mysql',
      'prefix' => '',
    ),
  ),
);

$dir = dirname(DRUPAL_ROOT);

// Use development service parameters.
$settings['container_yamls'][] = $dir . '/docroot/sites/development.services.yml';
$settings['container_yamls'][] = $dir . '/docroot/sites/blt.development.services.yml';

// Allow access to update.php.
$settings['update_free_access'] = TRUE;

/**
 * Assertions.
 *
 * The Drupal project primarily uses runtime assertions to enforce the
 * expectations of the API by failing when incorrect calls are made by code
 * under development.
 *
 * @see http://php.net/assert
 * @see https://www.drupal.org/node/2492225
 *
 * If you are using PHP 7.0 it is strongly recommended that you set
 * zend.assertions=1 in the PHP.ini file (It cannot be changed from .htaccess
 * or runtime) on development machines and to 0 in production.
 *
 * @see https://wiki.php.net/rfc/expectations
 */
assert_options(ASSERT_ACTIVE, TRUE);
Handle::register();

/**
 * Show all error messages, with backtrace information.
 *
 * In case the error level could not be fetched from the database, as for
 * example the database connection failed, we rely only on this value.
 */
$config['system.logging']['error_level'] = 'verbose';

/**
 * Disable CSS and JS aggregation.
 */
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

/**
 * Disable the render cache (this includes the page cache).
 *
 * Note: you should test with the render cache enabled, to ensure the correct
 * cacheability metadata is present. However, in the early stages of
 * development, you may want to disable it.
 *
 * This setting disables the render cache by using the Null cache back-end
 * defined by the development.services.yml file above.
 *
 * Do not use this setting until after the site is installed.
 */
// $settings['cache']['bins']['render'] = 'cache.backend.null';
/**
 * Disable Dynamic Page Cache.
 *
 * Note: you should test with Dynamic Page Cache enabled, to ensure the correct
 * cacheability metadata is present (and hence the expected behavior). However,
 * in the early stages of development, you may want to disable it.
 */
// $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
/**
 * Allow test modules and themes to be installed.
 *
 * Drupal ignores test modules and themes by default for performance reasons.
 * During development it can be useful to install test extensions for debugging
 * purposes.
 */
$settings['extension_discovery_scan_tests'] = FALSE;


/**
 * Configure static caches.
 *
 * Note: you should test with the config, bootstrap, and discovery caches enabled to 
 * test that metadata is cached as expected. However, in the early stages of development,
 * you may want to disable them. Overrides to these bins must be explicitly set for each 
 * bin to change the default configuration provided by Drupal core in core.services.yml. 
 * See https://www.drupal.org/node/2754947
 */

 // $settings['cache']['bins']['bootstrap'] = 'cache.backend.null';
 // $settings['cache']['bins']['discovery'] = 'cache.backend.null';
 // $settings['cache']['bins']['config'] = 'cache.backend.null';


/**
 * Enable access to rebuild.php.
 *
 * This setting can be enabled to allow Drupal's php and database cached
 * storage to be cleared via the rebuild.php page. Access to this page can also
 * be gained by generating a query string from rebuild_token_calculator.sh and
 * using these parameters in a request to rebuild.php.
 */
$settings['rebuild_access'] = FALSE;

/**
 * Skip file system permissions hardening.
 *
 * The system module will periodically check the permissions of your site's
 * site directory to ensure that it is not writable by the website user. For
 * sites that are managed with a version control system, this can cause problems
 * when files in that directory such as settings.php are updated, because the
 * user pulling in the changes won't have permissions to modify files in the
 * directory.
 */
$settings['skip_permissions_hardening'] = TRUE;

/**
 * Temporary file path:
 *
 * A local file system path where temporary files will be stored. This
 * directory should not be accessible over the web.
 *
 * Note: Caches need to be cleared when this value is changed.
 *
 * See https://www.drupal.org/node/1928898 for more information
 * about global configuration override.
 */
$config['system.file']['path']['temporary'] = '/tmp';

/**
 * Private file path.
 */
$settings['file_private_path'] = $dir . '/files-private';
if (isset($acsf_site_name)) {
  $settings['file_public_path'] = "sites/default/files/$acsf_site_name";
  $settings['file_private_path'] = "$repo_root/files-private/$acsf_site_name";
}

/**
 * Trusted host configuration.
 *
 * See full description in default.settings.php.
 */
$settings['trusted_host_patterns'] = array(
  '^.+$',
);
