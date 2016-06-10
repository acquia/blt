<?php

// Includes required Acquia configuration and set $base_url correctly.
require DRUPAL_ROOT . '/sites/default/settings/base.settings.php';

// Includes caching configuration.
require DRUPAL_ROOT . '/sites/default/settings/cache.settings.php';

// Includes logging configuration.
require DRUPAL_ROOT . '/sites/default/settings/logging.settings.php';

// Prevent APCu memory exhaustion.
$settings['container_yamls'][] = __DIR__ . '/apcu_fix.yml';
