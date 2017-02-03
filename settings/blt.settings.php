<?php

/*******************************************************************************
 * Setup BLT utility variables.
 ******************************************************************************/

// We assume that we are operating against the default site dir. You may alternatively set this variable before including blt.settings.php.
$site_dir = !empty($site_dir) ? $site_dir : 'default';

/**
 * Host detection.
 */
if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
  $forwarded_host = $_SERVER['HTTP_X_FORWARDED_HOST'];
}
elseif(!empty($_SERVER['HTTP_HOST'])) {
  $forwarded_host = $_SERVER['HTTP_HOST'];
}
else {
  $forwarded_host = NULL;
}

$server_protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
$forwarded_protocol = !empty($_ENV['HTTP_X_FORWARDED_PROTO']) ? $_ENV['HTTP_X_FORWARDED_PROTO'] : $server_protocol;

/**
 * Environment detection.
 *
 * Note that the values of enviromental variables are set differently on Acquia
 * Cloud Free tier vs Acquia Cloud Professional and Enterprise.
 */
$ah_env = isset($_ENV['AH_SITE_ENVIRONMENT']) ? $_ENV['AH_SITE_ENVIRONMENT'] : NULL;
$ah_group = isset($_ENV['AH_SITE_GROUP']) ? $_ENV['AH_SITE_GROUP'] : NULL;
$is_ah_env = (bool) $ah_env;
$is_ah_prod_env = ($ah_env == 'prod' || $ah_env == '01live');
$is_ah_stage_env = ($ah_env == 'test' || $ah_env == '01test');
$is_ah_dev_cloud = (!empty($_SERVER['HTTP_HOST']) && strstr($_SERVER['HTTP_HOST'], 'devcloud'));
$is_ah_dev_env = (preg_match('/^dev[0-9]*$/', $ah_env) || $ah_env == '01dev');
$is_acsf = (!empty($ah_group) && file_exists("/mnt/files/$ah_group.$ah_env/files-private/sites.json"));
$acsf_db_name = $is_acsf ? $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'] : NULL;
$is_local_env = !$is_ah_env;

/*******************************************************************************
 * BLT includes & BLT default configuration.
 ******************************************************************************/

// Includes caching configuration.
require __DIR__ . '/cache.settings.php';

// Includes logging configuration.
require __DIR__ . '/logging.settings.php';

// Includes filesystem configuration.
require __DIR__ . '/filesystem.settings.php';

// Prevent APCu memory exhaustion.
$settings['container_yamls'][] = __DIR__ . '/apcu_fix.yml';

// Include simplesamlphp settings if the file exists.
if (file_exists(__DIR__ . '/simplesamlphp.settings.php')) {
  require __DIR__ . '/simplesamlphp.settings.php';
}

/**
 * Salt for one-time login links, cancel links, form tokens, etc.
 *
 * This variable will be set to a random value by the installer. All one-time
 * login links will be invalidated if the value is changed. Note that if your
 * site is deployed on a cluster of web servers, you must ensure that this
 * variable has the same value on each server.
 *
 * For enhanced security, you may set this variable to the contents of a file
 * outside your document root; you should also ensure that this file is not
 * stored with backups of your database.
 *
 * Example:
 * @code
 *   $settings['hash_salt'] = file_get_contents('/home/example/salt.txt');
 * @endcode
 */
$settings['hash_salt'] = file_get_contents(DRUPAL_ROOT . '/../salt.txt');

/*******************************************************************************
 * Environment-specific includes.
 ******************************************************************************/

/**
 * Acquia Cloud settings.
 */
if ($is_ah_env) {
  if (!$is_acsf && file_exists('/var/www/site-php') && $site_dir == 'default') {
    require "/var/www/site-php/{$_ENV['AH_SITE_GROUP']}/{$_ENV['AH_SITE_GROUP']}-settings.inc";
  }

  // Store API Keys and things outside of version control.
  // @see settings/sample-secrets.settings.php for sample code.
  $secrets_file = sprintf('/mnt/gfs/%s.%s/secrets.settings.php', $_ENV['AH_SITE_GROUP'], $_ENV['AH_SITE_ENVIRONMENT']);
  if (file_exists($secrets_file)) {
    require $secrets_file;
  }
}

/**
 * Include optional site specific includes file.
 *
 * This is being included before the local file so all available settings are
 * able to be overridden in the local.settings.php file below.
 */
if (file_exists(DRUPAL_ROOT . "/sites/$site_dir/settings/includes.settings.php")) {
  require DRUPAL_ROOT . "/sites/$site_dir/settings/includes.settings.php";
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
  if (file_exists(DRUPAL_ROOT . "/sites/$site_dir/settings/local.settings.php")) {
    require DRUPAL_ROOT . "/sites/$site_dir/settings/local.settings.php";
  }

  // Load Acquia Pipeline settings.
  if (getenv('PIPELINE_ENV') && file_exists(__DIR__ . '/pipelines.settings.php')) {
    require __DIR__ . '/pipelines.settings.php';
  }
  // Load Travis CI settings.
  elseif (getenv('TRAVIS') && file_exists(__DIR__ . '/travis.settings.php')) {
    require __DIR__ . '/travis.settings.php';
  }
  // Load Tugboat settings.
  elseif (getenv('TUGBOAT_URL') && file_exists(__DIR__ . '/tugboat.settings.php')) {
    require __DIR__ . '/tugboat.settings.php';
  }
}
