<?php

/**
 * @file
 * Contains configuration for the Drupal 8 Memcache API and Integration and
 * settings for using Memcached on Acquia Cloud.
 *
 * Note: the memcache module must be enabled before using memcache as a backend.
 *
 * @see: https://api.drupal.org/api/drupal/core%21core.api.php/group/cache/8.4.x
 * @see: https://www.drupal.org/node/2754947
 * @see: http://cgit.drupalcode.org/memcache/tree/README.txt?h=8.x-2.x
 * @see: https://docs.acquia.com/cloud/performance/memcached
 */

use Drupal\Core\Config\BootstrapConfigStorageFactory;
use Drupal\Core\Database\Database;

if ($is_ah_env) {
  // Note that this connects the database, so has to go after any
  // $databases definitions. See: https://www.drupal.org/node/2766509
  Database::setMultipleConnectionInfo($databases);
  $bootstrap_config = BootstrapConfigStorageFactory::get($class_loader);
  $modules = $bootstrap_config->read('core.extension');

  if ($modules && isset($modules['module']['memcache'])) {
    // Use Memcached extension.
    $memcached_exists = class_exists('Memcached', FALSE);

    if ($memcached_exists) {
      $settings['memcache']['extension'] = 'Memcached';
      // $settings['memcache']['options'] = [
      //   \Memcached::OPT_BINARY_PROTOCOL => TRUE,
      //   \Memcached::OPT_TCP_NODELAY => TRUE,
      // ];
    }

    // Use memcache as the default bin.
    $settings['cache']['default'] = 'cache.backend.memcache';

    // Enable stampede protection.
    $settings['memcache']['stampede_protection'] = TRUE;
    // Move locks to memcache.
    $settings['container_yamls'][] = __DIR__ . '/memcache.yml';
  }
}
