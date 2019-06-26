<?php

/**
 * @file
 * Contains memcache configuration.
 */

use Composer\Autoload\ClassLoader;

// Don't assume container is injected.
$app_root = \Drupal::root();

// Check for PHP Memcached libraries.
$memcache_exists = class_exists('Memcache', FALSE);
$memcached_exists = class_exists('Memcached', FALSE);
$memcache_services_yml = $app_root . '/modules/contrib/memcache/memcache.services.yml';
$memcache_module_is_present = file_exists($memcache_services_yml);
if ($memcache_module_is_present && ($memcache_exists || $memcached_exists)) {
  // Use Memcached extension if available.
  if ($memcached_exists) {
    $settings['memcache']['extension'] = 'Memcached';
  }
  if (class_exists(ClassLoader::class)) {
    $class_loader = new ClassLoader();
    $class_loader->addPsr4('Drupal\\memcache\\', $app_root . '/modules/contrib/memcache/src');
    $class_loader->register();
    $settings['container_yamls'][] = $memcache_services_yml;

    // Acquia Default Settings for the memcache module
    // Default settings for the Memcache module.
    // Enable compression for PHP 7.
    $settings['memcache']['options'][Memcached::OPT_COMPRESSION] = TRUE;

    // Set key_prefix to avoid drush cr flushing all bins on multisite.
    $settings['memcache']['key_prefix'] = $conf['acquia_hosting_site_info']['db']['name'] . '_';

    // Settings for SASL Authenticated Memcached.
    $settings['memcache']['options'][Memcached::OPT_BINARY_PROTOCOL] = TRUE;

    // Bootstrap cache.container with memcache rather than database.
    $settings['bootstrap_container_definition'] = [
      'parameters' => [],
      'services' => [
        'database' => [
          'class' => 'Drupal\Core\Database\Connection',
          'factory' => 'Drupal\Core\Database\Database::getConnection',
          'arguments' => ['default'],
        ],
        'settings' => [
          'class' => 'Drupal\Core\Site\Settings',
          'factory' => 'Drupal\Core\Site\Settings::getInstance',
        ],
        'memcache.settings' => [
          'class' => 'Drupal\memcache\MemcacheSettings',
          'arguments' => ['@settings'],
        ],
        'memcache.backend.cache.factory' => [
          'class' => 'Drupal\memcache\Driver\MemcacheDriverFactory',
          'arguments' => ['@memcache.settings'],
        ],
        'memcache.backend.cache.container' => [
          'class' => 'Drupal\memcache\DrupalMemcacheFactory',
          'factory' => ['@memcache.backend.cache.factory', 'get'],
          'arguments' => ['container'],
        ],
        'lock.container' => [
          'class' => 'Drupal\memcache\Lock\MemcacheLockBackend',
          'arguments' => ['container', '@memcache.backend.cache.container'],
        ],
        'cache_tags_provider.container' => [
          'class' => 'Drupal\Core\Cache\DatabaseCacheTagsChecksum',
          'arguments' => ['@database'],
        ],
        'cache.container' => [
          'class' => 'Drupal\memcache\MemcacheBackend',
          'arguments' => [
            'container',
            '@memcache.backend.cache.container',
            '@cache_tags_provider.container',
            '@lock.container',
          ],
        ],
      ],
    ];

    // Use memcache for bootstrap, discovery, config instead of fast chained
    // backend to properly invalidate caches on multiple webs.
    // See https://www.drupal.org/node/2754947
    $settings['cache']['bins']['bootstrap'] = 'cache.backend.memcache';
    $settings['cache']['bins']['discovery'] = 'cache.backend.memcache';
    $settings['cache']['bins']['config'] = 'cache.backend.memcache';

    // Use memcache as the default bin.
    $settings['cache']['default'] = 'cache.backend.memcache';
  }
}
