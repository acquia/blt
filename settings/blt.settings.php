<?php

  /**
   * @file
   * Setup BLT utility variables, include required files.
   */

use Acquia\Blt\Robo\Config\ConfigInitializer;
use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Acquia\Blt\Robo\Exceptions\BltException;
use Drupal\Component\Utility\Bytes;
use Symfony\Component\Console\Input\ArgvInput;

/*******************************************************************************
 * Host forwarding.
 ******************************************************************************/

// Drupal 8 aliasing/importing.
// Must be declared in global scope.
$http_host = getenv('HTTP_HOST');
$request_method = getenv('REQUEST_METHOD');
$request_uri = getenv('REQUEST_URI');
$http_x_request_id = getenv('HTTP_X_REQUEST_ID');

// If trusted_reverse_proxy_ips is not defined, fail gracefully.
$trusted_reverse_proxy_ips = isset($trusted_reverse_proxy_ips) ? $trusted_reverse_proxy_ips : '';
if (!is_array($trusted_reverse_proxy_ips)) {
  $trusted_reverse_proxy_ips = [];
}

// Tell Drupal whether the client arrived via HTTPS. Ensure the
// request is coming from our load balancers by checking the IP address.
if (getenv('HTTP_X_FORWARDED_PROTO') == 'https'
 && getenv('REMOTE_ADDR')
 && in_array(getenv('REMOTE_ADDR'), $trusted_reverse_proxy_ips)) {
  $_ENV['HTTPS'] = 'on';
  $_SERVER['HTTPS'] = 'on';
  putenv('HTTPS=on');
}
$x_ips = getenv('HTTP_X_FORWARDED_FOR') ? explode(',', getenv('HTTP_X_FORWARDED_FOR')) : array();
$x_ips = array_map('trim', $x_ips);

// Add REMOTE_ADDR to the X-Forwarded-For in case it's an internal AWS address.
if (getenv('REMOTE_ADDR')) {
  $x_ips[] = getenv('REMOTE_ADDR');
}

// Check firstly for the bal and then check for an internal IP immediately.
$settings['reverse_proxy_addresses'] = array();
if ($ip = array_pop($x_ips)) {
  if (in_array($ip, $trusted_reverse_proxy_ips)) {
    if (!in_array($ip, $settings['reverse_proxy_addresses'])) {
      $settings['reverse_proxy_addresses'][] = $ip;
    }
    // We have a reverse proxy so turn the setting on.
    $settings['reverse_proxy'] = TRUE;

    // Get the next IP to test if it is internal.
    $ip = array_pop($x_ips);
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE)) {
      if (!in_array($ip, $settings['reverse_proxy_addresses'])) {
        $settings['reverse_proxy_addresses'][] = $ip;
      }
    }
  }
}

/*******************************************************************************
 * Environment detection.
 ******************************************************************************/

/**
 * CI envs.
 */
// TODO: Incorporate all of these as well?
$is_travis_env = isset($_ENV['TRAVIS']);
$is_pipelines_env = isset($_ENV['PIPELINE_ENV']);
$is_probo_env = isset($_ENV['PROBO_ENVIRONMENT']);
$is_tugboat_env = isset($_ENV['TUGBOAT_URL']);
$is_gitlab_env = isset($_ENV['GITLAB_CI']);
$is_ci_env = $is_travis_env || $is_pipelines_env || $is_probo_env || $is_tugboat_env || $is_gitlab_env || isset($_ENV['CI']);

/**
 * Acquia envs.
 *
 * Note that the values of environmental variables are set differently on Acquia
 * Cloud Free tier vs Acquia Cloud Professional and Enterprise.
 */
// TODO: Remove all of these variables, call detector methods directly instead?
$repo_root = dirname(DRUPAL_ROOT);
$ah_env = EnvironmentDetector::getAhEnv();
$ah_group = EnvironmentDetector::getAhGroup();
$ah_site = EnvironmentDetector::getAhSite();
$is_ah_env = EnvironmentDetector::isAhEnv();
$is_ah_prod_env = EnvironmentDetector::isAhProdEnv();
$is_ah_stage_env = EnvironmentDetector::isAhStageEnv();
$is_ah_dev_cloud = EnvironmentDetector::isAhDevCloud();
$is_ah_dev_env = EnvironmentDetector::isAhDevEnv();
$is_ah_ode_env = EnvironmentDetector::isAhOdeEnv();
try {
  $is_acsf_env = EnvironmentDetector::isAcsfEnv();
}
catch (BltException $exception) {
  trigger_error($exception->getMessage(), E_USER_WARNING);
}
// @todo Maybe check for acsf-tools.
$is_acsf_inited = file_exists(DRUPAL_ROOT . "/sites/g");
$acsf_db_name = isset($GLOBALS['gardens_site_settings']) && $is_acsf_env ? $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'] : NULL;

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
$is_local_env = !$is_ah_env && !$is_pantheon_env && !$is_ci_env;

/**
 * Common variables.
 */
$is_dev_env = $is_ah_dev_env || $is_pantheon_dev_env;
$is_stage_env = $is_ah_stage_env || $is_pantheon_stage_env;
$is_prod_env = $is_ah_prod_env || $is_pantheon_prod_env;

/**
 * Site directory detection.
 */
if (!isset($site_path)) {
  $site_path = \Drupal::service('site.path');
}
$site_dir = str_replace('sites/', '', $site_path);


/*******************************************************************************
 * Acquia Cloud Site Factory settings.
 ******************************************************************************/

if ($is_acsf_inited) {
  if ($is_local_env) {
    // When developing locally, we use the host name to determine which site
    // factory site is active. The hostname must have a corresponding entry
    // under the multisites key.
    $input = new ArgvInput(!empty($_SERVER['argv']) ? $_SERVER['argv'] : ['']);
    $config_initializer = new ConfigInitializer($repo_root, $input);
    $blt_config = $config_initializer->initialize();

    // The hostname must match the pattern local.[sitename].com, where
    // [sitename] is a value in the multisites array.
    $domain_fragments = explode('.', $http_host);
    $name = array_slice($domain_fragments, 1);
    $acsf_sites = $blt_config->get('multisites');
    if (in_array($name, $acsf_sites)) {
      $_acsf_site_name = $name;
    }
  }
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
  $group_settings_file = "/var/www/site-php/$ah_group/$ah_group-settings.inc";
  $site_settings_file = "/var/www/site-php/$ah_group/$site_dir-settings.inc";
  if (!$is_acsf_env && file_exists($group_settings_file)) {
    if ($site_dir == 'default') {
      require $group_settings_file;
    }
    // Includes multisite settings for given site.
    elseif (file_exists($site_settings_file)) {
      require $site_settings_file;
    }
  }

  // Store API Keys and things outside of version control.
  // @see settings/sample-secrets.settings.php for sample code.
  // @see https://docs.acquia.com/resource/secrets/#secrets-settings-php-file
  $secrets_file = "/mnt/files/$ah_group.$ah_env/secrets.settings.php";
  if (file_exists($secrets_file)) {
    require $secrets_file;
  }
  // Includes secrets file for given site.
  $site_secrets_file = "/mnt/files/$ah_group.$ah_env/$site_dir/secrets.settings.php";
  if (file_exists($site_secrets_file)) {
    require $site_secrets_file;
  }
}

/*******************************************************************************
 * BLT includes & BLT default configuration.
 ******************************************************************************/

// Prevent APCu memory exhaustion.
// Acquia assigns 8 MB for APCu, which is only adequate for small cache pools.
if (extension_loaded('apc') && ini_get('apc.enabled')) {
  $apc_shm_size = Bytes::toInt(ini_get('apc.shm_size'));
  $apcu_fix_size = Bytes::toInt('32M');
  if ($apc_shm_size < $apcu_fix_size) {
    $settings['container_yamls'][] = __DIR__ . '/apcu_fix.yml';
  }
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
 * Include custom global settings file.
 *
 * This provides an opportunity for applications to override any previous
 * configuration at a global or multisite level.
 *
 * This is being included before the CI and site specific files so all available
 * settings are able to be overridden in the includes.settings.php file below.
 */
if (file_exists(DRUPAL_ROOT . "/sites/settings/global.settings.php")) {
  require DRUPAL_ROOT . "/sites/settings/global.settings.php";
}

/*******************************************************************************
 * Environment-specific includes.
 ******************************************************************************/

/**
 * Load CI env includes.
 */
if ($is_ci_env) {
  require __DIR__ . '/ci.settings.php';
}

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
// Load GitLab settings.
elseif ($is_gitlab_env) {
  require __DIR__ . '/gitlab.settings.php';
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
