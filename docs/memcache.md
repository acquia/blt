# Memcache

This document describes how to configure the [Memcache API and Integration](https://www.drupal.org/project/memcache) (Memcache) module for Drupal 8. The Memcache module provides an API for using Memcached and the PECL Memcache or Memcached libraries with Drupal and provides backends for Drupal's caching and locking systems. The most complete and up to date documentation is included with the module, in the [README.txt](http://cgit.drupalcode.org/memcache/tree/README.txt?h=8.x-2.x) file.

## Acquia Cloud and Acquia Cloud Site Factory

To enable memcache integration in a Cloud hosting environment, ensure that you've installed the `acquia/memcache-settings` Composer package. If this package is present, BLT will automatically include the relevant settings files that enable memcache integration.

## Local Development

The below has been tested with DrupalVM as configured through BLT's `blt vm` command, but should also work for most CI environments where the memcache backend is localhost on port 11211.

Add the below statements to an environment's `local.settings.php` to use memcache as the default backend for Drupal's caching and locking systems. The memcache module does not need to be enabled with the snippet below, but may need to be if this configuration is removed. Note that the below configuration explicitly overrides the default bins for the discovery, bootstrap, and config cache bins because Drupal core permanently caches these static bins by default.

```
// Include a unique prefix for each local Drupal installation.
if ($is_local_env) {
  $settings['memcache']['key_prefix'] = $site_dir;
}

require DRUPAL_ROOT . "/../vendor/acquia/blt/settings/memcache.settings.php";
```
