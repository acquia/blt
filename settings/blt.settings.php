<?php

/**
 * @file
 * Setup BLT utility variables, include required files.
 */

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\DrupalKernel;
use Drupal\Component\Utility\Bytes;

/**
 * Host detection.
 */
if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
  $forwarded_host = $_SERVER['HTTP_X_FORWARDED_HOST'];
}
elseif (!empty($_SERVER['HTTP_HOST'])) {
  $forwarded_host = $_SERVER['HTTP_HOST'];
}
else {
  $forwarded_host = NULL;
}

$server_protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
$forwarded_protocol = !empty($_ENV['HTTP_X_FORWARDED_PROTO']) ? $_ENV['HTTP_X_FORWARDED_PROTO'] : $server_protocol;

/*******************************************************************************
 * Environment detection.
 ******************************************************************************/

/**
 * CI envs.
 */
$is_travis_env = isset($_ENV['TRAVIS']);
$is_pipelines_env = isset($_ENV['PIPELINE_ENV']);
$is_probo_env = isset($_ENV['PROBO_ENVIRONMENT']);
$is_tugboat_env = isset($_ENV['TUGBOAT_URL']);
$is_ci_env = $is_travis_env || $is_pipelines_env || $is_probo_env || $is_tugboat_env;

/**
 * Acquia envs.
 *
 * Note that the values of environmental variables are set differently on Acquia
 * Cloud Free tier vs Acquia Cloud Professional and Enterprise.
 */
$ah_env = isset($_ENV['AH_SITE_ENVIRONMENT']) ? $_ENV['AH_SITE_ENVIRONMENT'] : NULL;
$ah_group = isset($_ENV['AH_SITE_GROUP']) ? $_ENV['AH_SITE_GROUP'] : NULL;
$is_ah_env = (bool) $ah_env;
$is_ah_prod_env = ($ah_env == 'prod' || $ah_env == '01live');
$is_ah_stage_env = ($ah_env == 'test' || $ah_env == '01test' || $ah_env == 'stg');
$is_ah_dev_cloud = (!empty($_SERVER['HTTP_HOST']) && strstr($_SERVER['HTTP_HOST'], 'devcloud'));
$is_ah_dev_env = (preg_match('/^dev[0-9]*$/', $ah_env) || $ah_env == '01dev');
$is_ah_ode_env = (preg_match('/^ode[0-9]*$/', $ah_env));
$is_acsf = (!empty($ah_group) && file_exists("/mnt/files/$ah_group.$ah_env/files-private/sites.json"));
$acsf_db_name = $is_acsf ? $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'] : NULL;

/**
 * Pantheon envs.
 */
$is_pantheon_env = isset($_ENV['PANTHEON_ENVIRONMENT']);
$pantheon_env = $is_pantheon_env ? $_ENV['PANTHEON_ENVIRONMENT'] : NULL;
$is_pantheon_dev_env = $pantheon_env == 'dev';
$is_pantheon_stage_env = $pantheon_env == 'test';
$is_pantheon_prod_env = $pantheon_env == 'live';

/**
 * Local envs.
 */
$is_local_env = !$is_ah_env && !$is_pantheon_env;

/**
 * Common variables.
 */
$is_dev_env = $is_ah_dev_env || $is_pantheon_dev_env;
$is_stage_env = $is_ah_stage_env || $is_pantheon_stage_env;
$is_prod_env = $is_ah_prod_env || $is_pantheon_prod_env;

/**
 * Site directory detection.
 */
try {
  $site_path = DrupalKernel::findSitePath(Request::createFromGlobals());
}
catch (BadRequestHttpException $e) {
  $site_path = 'sites/default';
}
$site_dir = str_replace('sites/', '', $site_path);
// ACSF uses a pseudo-multisite architecture that places all site files under
// sites/g/files, which isn't useful for our purposes.
if ($is_acsf) {
  $site_dir = 'default';
}

/*******************************************************************************
 * Acquia Cloud settings.
 *
 * These includes are intentionally loaded before all others because we do not
 * have control over their contents. By loading all other includes after this,
 * we have the opportunity to override any configuration values provided by the
 * hosted files. This is not necessary for files that we control.
 ******************************************************************************/

if ($is_ah_env) {
  if (!$is_acsf && file_exists('/var/www/site-php')) {
    if ($site_dir == 'default') {
      require "/var/www/site-php/{$_ENV['AH_SITE_GROUP']}/{$_ENV['AH_SITE_GROUP']}-settings.inc";
    }
    // Includes multisite settings for given site.
    elseif (file_exists("/var/www/site-php/{$_ENV['AH_SITE_GROUP']}/$site_dir-settings.inc")) {
      require "/var/www/site-php/{$_ENV['AH_SITE_GROUP']}/$site_dir-settings.inc";
    }
  }

  // Store API Keys and things outside of version control.
  // @see settings/sample-secrets.settings.php for sample code.
  $secrets_file = sprintf("/mnt/gfs/%s.%s/secrets.settings.php", $_ENV['AH_SITE_GROUP'], $_ENV['AH_SITE_ENVIRONMENT']);
  if (file_exists($secrets_file)) {
    require $secrets_file;
  }
  // Includes secrets file for given site.
  $site_secrets_file = sprintf("/mnt/gfs/%s.%s/$site_dir/secrets.settings.php", $_ENV['AH_SITE_GROUP'], $_ENV['AH_SITE_ENVIRONMENT']);
  if (file_exists($site_secrets_file)) {
    require $site_secrets_file;
  }
}

/*******************************************************************************
 * BLT includes & BLT default configuration.
 ******************************************************************************/

// Prevent APCu memory exhaustion.
// Acquia assigns 8 MB for APCu, which is only adequate for small cache pools.
$apc_shm_size = Bytes::toInt(ini_get('apc.shm_size'));
$apcu_fix_size = Bytes::toInt('32M');
if ($apc_shm_size < $apcu_fix_size) {
  $settings['container_yamls'][] = __DIR__ . '/apcu_fix.yml';
}

// Includes caching configuration.
require __DIR__ . '/cache.settings.php';

// Includes configuration management settings.
require __DIR__ . '/config.settings.php';

// Includes logging configuration.
require __DIR__ . '/logging.settings.php';

// Includes filesystem configuration.
require __DIR__ . '/filesystem.settings.php';

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

/**
 * Deployment identifier.
 *
 * Drupal's dependency injection container will be automatically invalidated and
 * rebuilt when the Drupal core version changes. When updating contributed or
 * custom code that changes the container, changing this identifier will also
 * allow the container to be invalidated as soon as code is deployed.
 */
$settings['deployment_identifier'] = \Drupal::VERSION;
$deploy_id_file = DRUPAL_ROOT . '/../deployment_identifier';
if (file_exists($deploy_id_file)) {
  $settings['deployment_identifier'] = file_get_contents($deploy_id_file);
}

/**
 * Include custom global settings files.
 *
 * This is intended for to provide an opportunity for applications to override
 * any previous configuration at a global or multisite level.
 *
 * This is being included before the CI and site specific files so all available
 * settings are able to be overridden in the includes.settings.php file below.
 */
if ($settings_files = glob(DRUPAL_ROOT . "/sites/settings/*.settings.php")) {
  foreach ($settings_files as $settings_file) {
    require $settings_file;
  }
}

/*******************************************************************************
 * Environment-specific includes.
 ******************************************************************************/

/**
 * Load CI env includes.
 */

// Load Acquia Pipeline settings.
if ($is_pipelines_env) {
  require __DIR__ . '/pipelines.settings.php';
}
// Load Travis CI settings.
elseif ($is_travis_env) {
  require __DIR__ . '/travis.settings.php';
}
// Load Tugboat settings.
elseif ($is_tugboat_env) {
  require __DIR__ . '/tugboat.settings.php';
}
// Load Probo settings.
elseif ($is_probo_env) {
  require __DIR__ . '/probo.settings.php';
}

/**
 * Include optional site specific includes file.
 *
 * This is intended for to provide an opportunity for applications to override
 * any previous configuration.
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
 * This is intended to provide an opportunity for local environments to override
 * any previous configuration.
 *
 * Use local.settings.php to override variables on secondary (staging,
 * development, etc) installations of this site. Typically used to disable
 * caching, JavaScript/CSS compression, re-routing of outgoing emails, and
 * other things that should not happen on development and testing sites.
 *
 * Keep this code block at the end of this file to take full effect.
 */
if ($is_local_env) {
  // Load local settings for all sites.
  if (file_exists(DRUPAL_ROOT . "/sites/settings/local.settings.php")) {
    require DRUPAL_ROOT . "/sites/settings/local.settings.php";
  }
  // Load local settings for given single.
  if (file_exists(DRUPAL_ROOT . "/sites/$site_dir/settings/local.settings.php")) {
    require DRUPAL_ROOT . "/sites/$site_dir/settings/local.settings.php";
  }
}
