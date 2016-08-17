<?php

// Includes required Acquia configuration and set $base_url correctly.
require_once DRUPAL_ROOT . '/sites/default/settings/base.settings.php';

// Includes caching configuration.
require_once DRUPAL_ROOT . '/sites/default/settings/cache.settings.php';

// Includes logging configuration.
require_once DRUPAL_ROOT . '/sites/default/settings/logging.settings.php';


/**
 * Acquia Cloud settings.
 */
if ($is_ah_env && file_exists('/var/www/site-php')) {
  require_once "/var/www/site-php/{$_ENV['AH_SITE_GROUP']}/{$_ENV['AH_SITE_GROUP']}-settings.inc";

  // Store API Keys and things outside of version control.
  // @see settings/sample-secrets.settings.php for sample code.
  $secrets_file = sprintf('/mnt/gfs/%s.%s/secrets.settings.php', $_ENV['AH_SITE_GROUP'], $_ENV['AH_SITE_ENVIRONMENT']);
  if (file_exists($secrets_file)) {
    require_once $secrets_file;
  }
}

/**
 * Load local development override configuration, if available.
 *
 * Use local.settings.php to override variables on secondary (staging,
 * development, etc) installations of this site. Typically used to disable
 * caching, JavaScript/CSS compression, re-routing of outgoing emails, and
 * other things that should not happen on development and testing sites.
 *
 * Keep this code block at the end of this file to take full effect.
 */
if ($is_local_env) {
  // Load Dev Desktop settings.
  if (isset($_SERVER['DEVDESKTOP_DRUPAL_SETTINGS_DIR']) && file_exists($_SERVER['DEVDESKTOP_DRUPAL_SETTINGS_DIR'] . '/loc_${project.machine_name}_dd.inc')) {
    require_once $_SERVER['DEVDESKTOP_DRUPAL_SETTINGS_DIR'] . '/loc_${project.machine_name}_dd.inc';
  }
  // Load local machine settings.
  elseif (file_exists(DRUPAL_ROOT . '/sites/default/settings/local.settings.php')) {
    require_once DRUPAL_ROOT . '/sites/default/settings/local.settings.php';
  }

  // Load Travis CI settings.
  if (getenv('TRAVIS') && file_exists(DRUPAL_ROOT . '/sites/default/settings/travis.settings.php')) {
    require_once DRUPAL_ROOT . '/sites/default/settings/travis.settings.php';
  }
  // Load Tugboat settings.
  elseif (getenv('TUGBOAT_URL') && file_exists(DRUPAL_ROOT . '/sites/default/settings/tugboat.settings.php')) {
    require_once DRUPAL_ROOT . '/sites/default/settings/tugboat.settings.php';
  }
}
