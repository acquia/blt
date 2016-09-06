<?php

// Includes required Acquia configuration and set $base_url correctly.
require DRUPAL_ROOT . '/sites/default/settings/base.settings.php';

// Includes caching configuration.
require DRUPAL_ROOT . '/sites/default/settings/cache.settings.php';

// Includes logging configuration.
require DRUPAL_ROOT . '/sites/default/settings/logging.settings.php';

// Includes filesystem configuration.
require_once DRUPAL_ROOT . '/sites/default/settings/filesystem.settings.php';


/**
 * Acquia Cloud settings.
 */
if ($is_ah_env && file_exists('/var/www/site-php')) {
  require "/var/www/site-php/{$_ENV['AH_SITE_GROUP']}/{$_ENV['AH_SITE_GROUP']}-settings.inc";

  // Store API Keys and things outside of version control.
  // @see settings/sample-secrets.settings.php for sample code.
  $secrets_file = sprintf('/mnt/gfs/%s.%s/secrets.settings.php', $_ENV['AH_SITE_GROUP'], $_ENV['AH_SITE_ENVIRONMENT']);
  if (file_exists($secrets_file)) {
    require $secrets_file;
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
  // Load local machine settings.
  if (file_exists(DRUPAL_ROOT . '/sites/default/settings/local.settings.php')) {
    require DRUPAL_ROOT . '/sites/default/settings/local.settings.php';
  }

  // Load Acquia Pipeline settings.
  if (getenv('PIPELINE_ENV') && file_exists(DRUPAL_ROOT . '/sites/default/settings/pipelines.settings.php')) {
    require DRUPAL_ROOT . '/sites/default/settings/pipelines.settings.php';
  }
  // Load Travis CI settings.
  elseif (getenv('TRAVIS') && file_exists(DRUPAL_ROOT . '/sites/default/settings/travis.settings.php')) {
    require DRUPAL_ROOT . '/sites/default/settings/travis.settings.php';
  }
  // Load Tugboat settings.
  elseif (getenv('TUGBOAT_URL') && file_exists(DRUPAL_ROOT . '/sites/default/settings/tugboat.settings.php')) {
    require DRUPAL_ROOT . '/sites/default/settings/tugboat.settings.php';
  }
}
