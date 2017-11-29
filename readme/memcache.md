# Memcache

This document describes how to configure the [Memcache API and Integration](https://www.drupal.org/project/memcache) (Memcache) module for Drupal 8. The Memcache module provides an API for using Memcached and the PECL Memcache or Memcached libraries with Drupal and provides backends for Drupal's caching and locking systems. The most complete and up to date documentation is included with the module, in the [README.txt](http://cgit.drupalcode.org/memcache/tree/README.txt?h=8.x-2.x) file.

Before enabling the Memcache module, it is important to understand how the Drupal 8 [Cache API](https://api.drupal.org/api/drupal/core%21core.api.php/group/cache/8.4.x) functions and the how Drupal determines which cache backend to use for a specific cache bin, see [#2754947](https://www.drupal.org/node/2754947). Note: Drupal 8 does not gracefully handle configurations where a given cache backend is set as default, but the module providing the backend is not enabled [#2766509](https://www.drupal.org/node/2766509).

## Acquia Cloud

[Using Memcached on Acquia Cloud](https://docs.acquia.com/cloud/performance/memcached) provides detailed information regarding how Acquia supports Memcached for its subscriptions and products, and is a good resource in general for information regarding Drupal and Memcache integrations. It is important that the settings for `memcache_key_prefix` and `memcache_servers` not be modified on Acquia Cloud.

BLT treats Memcache module integration as opt-in as it can be used only in environments that have Drupal installed and the Memcache module enabled, and won't need to be reinstalled, e.g. stage and prod. The snippet below should be customized as need and added to a `settings.php` for an environment meeting these criteria.

```
if ($is_ah_env) {
  switch ($ah_env) {
    case 'test':
    case 'prod':
      // Use Memcached extension.
      $memcached_exists = class_exists('Memcached', FALSE);
      if ($memcached_exists) {
        $settings['memcache']['extension'] = 'Memcached';
      }

      // Use memcache as the default bin.
      $settings['cache']['default'] = 'cache.backend.memcache';

      // Enable stampede protection.
      $settings['memcache']['stampede_protection'] = TRUE;
      // Move locks to memcache.
      $settings['container_yamls'][] = DRUPAL_ROOT . '/../vendor/acquia/blt/settings/memcache.yml';
    break;
  }
}
```

## Local Development

The below has been tested with DrupalVM as configured through BLT's `blt vm` command, but should also work for most CI environments where the memcache backend is localhost on port 11211.

Add the below statements to an environment's `local.settings.php` after Drupal is installed and the Memcache module is enabled.

```
// Include a unique prefix for each local Drupal installation.
if ($is_local_env) {
  $settings['memcache']['key_prefix'] = $site_dir;
}

// Use Memcached extension.
$memcached_exists = class_exists('Memcached', FALSE);
if ($memcached_exists) {
  $settings['memcache']['extension'] = 'Memcached';
}

// Use memcache as the default bin.
$settings['cache']['default'] = 'cache.backend.memcache';

// Enable stampede protection.
$settings['memcache']['stampede_protection'] = TRUE;
// Move locks to memcache.
$settings['container_yamls'][] = DRUPAL_ROOT . '/../vendor/acquia/blt/settings/memcache.yml';
```
