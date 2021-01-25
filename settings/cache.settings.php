<?php

/**
 * @file
 * Contains caching configuration.
 */

/**
 * Use memcache as cache backend if Acquia configuration is present.
 */
$repo_root = dirname(DRUPAL_ROOT);
$memcache_settings_file = $repo_root . '/vendor/acquia/memcache-settings/memcache.settings.php';
if (file_exists($memcache_settings_file)) {
  // phpcs:ignore
  require_once $memcache_settings_file;
}
