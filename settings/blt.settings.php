<?php

use Acquia\Blt\Robo\Config\ConfigInitializer;
use Symfony\Component\Console\Input\ArgvInput;

/*******************************************************************************
 * Setup BLT utility variables.
 ******************************************************************************/

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
$repo_root = dirname(DRUPAL_ROOT);
$ah_env = isset($_ENV['AH_SITE_ENVIRONMENT']) ? $_ENV['AH_SITE_ENVIRONMENT'] : NULL;
$ah_group = isset($_ENV['AH_SITE_GROUP']) ? $_ENV['AH_SITE_GROUP'] : NULL;
$ah_site = isset($_ENV['AH_SITE_NAME']) ? $_ENV['AH_SITE_NAME'] : NULL;
$is_ah_env = (bool) $ah_env;
$is_ah_prod_env = ($ah_env == 'prod' || $ah_env == '01live');
$is_ah_stage_env = ($ah_env == 'test' || $ah_env == '01test' || $ah_env == 'stg');
$is_ah_dev_cloud = (!empty($_SERVER['HTTP_HOST']) && strstr($_SERVER['HTTP_HOST'], 'devcloud'));
$is_ah_dev_env = (preg_match('/^dev[0-9]*$/', $ah_env) || $ah_env == '01dev');
$is_ah_ode_env = (preg_match('/^ode[0-9]*$/', $ah_env));
$is_acsf_env = (!empty($ah_group) && file_exists("/mnt/files/$ah_group.$ah_env/files-private/sites.json"));
// @todo Maybe check for acsf-tools.
$is_acsf_inited = file_exists(DRUPAL_ROOT . "/sites/g");
$acsf_db_name = $is_acsf_env ? $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'] : NULL;
$is_local_env = !$is_ah_env;

/**
 * Site directory detection.
 */
try {
  $site_path = \Drupal\Core\DrupalKernel::findSitePath(\Symfony\Component\HttpFoundation\Request::createFromGlobals());
}
catch (\Symfony\Component\HttpKernel\Exception\BadRequestHttpException $e) {
  $site_path = 'sites/default';
}
$site_dir = str_replace('sites/', '', $site_path);

/*******************************************************************************
 * Acquia Cloud Site Factory settings.
 ******************************************************************************/

if ($is_acsf_inited) {
  if ($is_local_env) {
    $input = new ArgvInput($_SERVER['argv']);
    $config_initializer = new ConfigInitializer($repo_root, $input);
    $config = $config_initializer->initialize();

    $name = substr($_SERVER['HTTP_HOST'],0, strpos($_SERVER['HTTP_HOST'],'.local'));
    $acsf_sites = $config->get('acsf.sites');
    if (in_array($name, $acsf_sites)) {
      $acsf_site_name = $name;
    }
  }
  elseif ($is_acsf_env && function_exists('gardens_site_data_load_file')) {
    // Function gardens_site_data_load_file() lives in
    // /mnt/www/html/$ah_site/docroot/sites/g/sites.inc
    if (($map = gardens_site_data_load_file()) && isset($map['sites'])) {
      foreach ($map['sites'] as $domain => $site_details) {
        if ($acsf_db_name == $site_details['name']) {
          $acsf_site_name = $domain;
          break;
        }
      }
    }

    // ACSF uses a pseudo-multisite architecture that places all site files under
    // sites/g/files.
    $site_dir = 'default';
  }
}

/*******************************************************************************
 * Acquia Cloud settings.
 ******************************************************************************/

if ($is_ah_env) {
  if (!$is_acsf_env && file_exists('/var/www/site-php')) {
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
  $secrets_file = sprintf('/mnt/gfs/%s.%s/secrets.settings.php', $_ENV['AH_SITE_GROUP'], $_ENV['AH_SITE_ENVIRONMENT']);
  if (file_exists($secrets_file)) {
    require $secrets_file;
  }
}

/*******************************************************************************
 * BLT includes & BLT default configuration.
 ******************************************************************************/

// Includes caching configuration.
require __DIR__ . '/cache.settings.php';

// Includes configuration management settings.
require __DIR__ . '/config.settings.php';

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

/*******************************************************************************
 * Environment-specific includes.
 ******************************************************************************/

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
  // Load Probo settings.
  elseif (getenv('PROBO_ENVIRONMENT') && file_exists(__DIR__ . '/probo.settings.php')) {
    require __DIR__ . '/probo.settings.php';
  }
  elseif ($is_acsf_inited) {
    require __DIR__ . '/acsf.local.settings.php';
  }

}
