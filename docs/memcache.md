# Memcache

This document describes how to configure the [Memcache API and Integration](https://www.drupal.org/project/memcache) (Memcache) module for Drupal 8. The Memcache module provides an API for using Memcached and the PECL Memcache or Memcached libraries with Drupal and provides backends for Drupal's caching and locking systems. The most complete and up to date documentation is included with the module, in the [README.txt](http://cgit.drupalcode.org/memcache/tree/README.txt?h=8.x-2.x) file.

Before enabling the Memcache module, it is important to understand how the Drupal 8 [Cache API](https://api.drupal.org/api/drupal/core%21core.api.php/group/cache/8.4.x) functions and the how Drupal determines which cache backend to use for a specific cache bin, see [#2754947](https://www.drupal.org/node/2754947). Note: Drupal 8 does not gracefully handle configurations where a given cache backend is set as default, but the module providing the backend is not enabled [#2766509](https://www.drupal.org/node/2766509). The snippets below provide logic that allows for using memcache as a cache backend if the memcached extension is available and the Drupal module exists in the codebase but is not yet enabled. This negates the need to patch core for graceful fallback and also allows for purging stale cache objects when the service definition container is updated on site install and/or deployments. This allows for using alternative cache bins such as memcache on site install and deployments as needed. This can help resolve site installation and deploy issues caused by cache race conditions. This is common on multisite applications using the content_translation module where the service container contains negotiation methods that override a locked default language on site install.

## Acquia Cloud

[Using Memcached on Acquia Cloud](https://docs.acquia.com/acquia-cloud/performance/memcached/) provides detailed information regarding how Acquia supports Memcached for its subscriptions and products, and is a good resource in general for information regarding Drupal and Memcache integrations. It is important that the settings for `memcache_key_prefix` and `memcache_servers` not be modified on Acquia Cloud.

BLT modifies the Memcache module integration on Acquia Cloud. BLT's configuration explicitly overrides the default bins for the discovery, bootstrap, and config cache bins because Drupal core permanently caches these static bins by default. This is required for rebuilding service definitions accurately on cache rebuilds and deploys. See [caching.settings.php](/settings/cache.settings.php).

## Acquia Cloud Site Factory

As of BLT 9.2, the factory hooks contain the necessary code to handle memcache integration with ACSF provided that your subscription and hardware are properly configured. [Using Memcached on Acquia Cloud](https://docs.acquia.com/acquia-cloud/performance/memcached/) provides additional information about this.

If you are upgrading from a previous version of BLT to 9.2.x, make sure and re-generate your factory hooks using:

```
recipes:acsf:init:hooks
``` 

This will create a new memcache factory hook for use on ACSF.


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
