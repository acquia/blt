<?php

/**
 * @file
 * Contains caching configuration.
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;

/**
 * Settings.
 *
 * @var array $settings
 */

if (EnvironmentDetector::isProdEnv() || EnvironmentDetector::isStageEnv()) {
  $config['system.logging']['error_level'] = 'hide';
}

/**
 * Use memcache as cache backend if Acquia configuration is present.
 */
$repo_root = dirname(DRUPAL_ROOT);
$memcacheSettingsFile = $repo_root . '/vendor/acquia/memcache-settings/memcache.settings.php';
if (file_exists($memcacheSettingsFile)) {
  // phpcs:ignore
  require $memcacheSettingsFile;
}
