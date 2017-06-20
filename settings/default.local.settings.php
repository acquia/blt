<?php

/**
 * @file
 * Local development override configuration feature.
 */

/**
 * Database configuration.
 */
$databases = array(
  'default' =>
  array(
    'default' =>
    array(
      'database' => 'drupal',
      'username' => 'drupal',
      'password' => 'drupal',
      'host' => 'localhost',
      'port' => '3306',
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
\Drupal\Component\Assertion\Handle::register();

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
# $settings['cache']['bins']['render'] = 'cache.backend.null';

/**
 * Disable Dynamic Page Cache.
 *
 * Note: you should test with Dynamic Page Cache enabled, to ensure the correct
 * cacheability metadata is present (and hence the expected behavior). However,
 * in the early stages of development, you may want to disable it.
 */
# $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';

/**
 * Allow test modules and themes to be installed.
 *
 * Drupal ignores test modules and themes by default for performance reasons.
 * During development it can be useful to install test extensions for debugging
 * purposes.
 */
$settings['extension_discovery_scan_tests'] = FALSE;

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

/**
 * Trusted host configuration.
 *
 * See full description in default.settings.php.
 */
$settings['trusted_host_patterns'] = array(
  '^.+$',
);
