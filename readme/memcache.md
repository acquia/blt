# Memcache

This document describes how to configure the [Memcache API and Integration](https://www.drupal.org/project/memcache) (Memcache) module for Drupal 8. The Memcache module provides an API for using Memcached and the PECL Memcache or Memcached libraries with Drupal and provides backends for Drupal's caching and locking systems. The most complete and up to date documentation is included with the module, in the [README.txt](http://cgit.drupalcode.org/memcache/tree/README.txt?h=8.x-2.x) file.

Before enabling the Memcache module, it is important to understand how the Drupal 8 [Cache API](https://api.drupal.org/api/drupal/core%21core.api.php/group/cache/8.4.x) functions and the how Drupal determines which cache backend to use for a specific cache bin, see [#2754947](https://www.drupal.org/node/2754947). Drupal 8 does not gracefully handle configurations where a given cache backend is set as default, but the module providing the backend is not enabled. BLT implements a conditional check for whether or not the Memcache module is enabled before attempting to use it as the default cache backend. This check will likely be removed after [#2766509](https://www.drupal.org/node/2766509) is landed in core.

## Acquia Cloud

[Using Memcached on Acquia Cloud](https://docs.acquia.com/cloud/performance/memcached) provides detailed information regarding how Acquia supports Memcached for its subscriptions and products, and is a good resource in general for information regarding Drupal and Memcache integrations. It is important that the settings for `memcache_key_prefix` and `memcache_servers` not be modified on Acquia Cloud.

If using a supported Acquia product or service, SASL authentication support in the Drupal Memcache module can be enabled by updating and placing the below in correct site-specific settings.php file, ideally one that is not committed to version control.

```
if ($is_ah_env) {
  $settings['memcache']['sasl'] = [
  'username' => 'yourSASLUsername',
  'password' => 'yourSASLPassword',
  ];
  $settings['memcache']['options'] = [
    \Memcached::OPT_BINARY_PROTOCOL => TRUE,
  ];
}
```

## Local Development

The below has been tested with DrupalVM as configured through BLT's `blt vm` command, but should also work for most CI environments where the memcache backend is localhost on port 11211. Note: the below example code will likely be modified after [#2766509](https://www.drupal.org/node/2766509) is landed in Drupal core.

Add the below statements to an environment's `local.settings.php` file after the database configuration settings. Note that the below differs slightly from the version BLT provides for use with Acquia Cloud.

```
// Note that this connects the database, so has to go after any
// $databases definitions. See: https://www.drupal.org/node/2766509
\Drupal\Core\Database\Database::setMultipleConnectionInfo($databases);
$bootstrap_config = \Drupal\Core\Config\BootstrapConfigStorageFactory::get($class_loader);
$modules = $bootstrap_config->read('core.extension');

if ($modules && isset($modules['module']['memcache'])) {
  // Use Memcached extension.
  $memcached_exists = class_exists('Memcached', FALSE);

  // Include a unique prefix for each local Drupal installation.
  if ($is_local_env) {
    // Do not modify the memcache_key_prefix on Acquia Cloud.
    $settings['memcache']['key_prefix'] = $site_dir;
  }

  if ($memcached_exists) {
    $settings['memcache']['extension'] = 'Memcached';
  }

  // Use memcache as the default bin.
  $settings['cache']['default'] = 'cache.backend.memcache';

  // Enable stampede protection.
  $settings['memcache']['stampede_protection'] = TRUE;
  // Move locks to memcache.
  $settings['container_yamls'][] = DRUPAL_ROOT . '/../vendor/acquia/blt/settings/memcache.yml';
}
```