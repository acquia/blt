<?php

/**
 * @file
 * Contains caching configuration.
 */

/**
 * Use memcache as cache backend if Acquia configuration is present.
 */
$repo_root = dirname(DRUPAL_ROOT);
$memcacheSettingsFile = $repo_root . '/vendor/acquia/memcache-settings/memcache.settings.php';
if (file_exists($memcacheSettingsFile)) {
  // phpcs:ignore
  require $memcacheSettingsFile;
}
