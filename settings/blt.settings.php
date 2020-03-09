<?php

/**
 * @file
 * Setup BLT utility variables, include required files.
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Acquia\Blt\Robo\Config\ConfigInitializer;
use Acquia\Blt\Robo\Exceptions\BltException;
use Symfony\Component\Console\Input\ArgvInput;

/**
 * Detect environments, sites, and hostnames.
 */

$http_host = getenv('HTTP_HOST');
$request_method = getenv('REQUEST_METHOD');
$request_uri = getenv('REQUEST_URI');
$http_x_request_id = getenv('HTTP_X_REQUEST_ID');

// If trusted_reverse_proxy_ips is not defined, fail gracefully.
// phpcs:ignore
$trusted_reverse_proxy_ips = isset($trusted_reverse_proxy_ips) ? $trusted_reverse_proxy_ips : '';
if (!is_array($trusted_reverse_proxy_ips)) {
  $trusted_reverse_proxy_ips = [];
}

// Tell Drupal whether the client arrived via HTTPS. Ensure the
// request is coming from our load balancers by checking the IP address.
if (getenv('HTTP_X_FORWARDED_PROTO') == 'https'
 && getenv('REMOTE_ADDR')
 && in_array(getenv('REMOTE_ADDR'), $trusted_reverse_proxy_ips)) {
  putenv("HTTPS=on");
  $_SERVER['HTTPS'] = 'on';
  putenv('HTTPS=on');
}
$x_ips = getenv('HTTP_X_FORWARDED_FOR') ? explode(',', getenv('HTTP_X_FORWARDED_FOR')) : [];
$x_ips = array_map('trim', $x_ips);

// Add REMOTE_ADDR to the X-Forwarded-For in case it's an internal AWS address.
if (getenv('REMOTE_ADDR')) {
  $x_ips[] = getenv('REMOTE_ADDR');
}

// Check firstly for the bal and then check for an internal IP immediately.
$settings['reverse_proxy_addresses'] = [];
$ip = array_pop($x_ips);
if ($ip) {
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

$repo_root = dirname(DRUPAL_ROOT);
/**
 * Site path.
 *
 * @var $site_path
 * This is always set and exposed by the Drupal Kernel.
 */
// phpcs:ignore
$site_dir = str_replace('sites/', '', $site_path);

// Special site name detection for ACSF sites being developed locally.
if (EnvironmentDetector::isAcsfInited()) {
  if (EnvironmentDetector::isLocalEnv()) {
    // When developing locally, we use the host name to determine which site
    // factory site is active. The hostname must have a corresponding entry
    // under the multisites key.
    $input = new ArgvInput(!empty($_SERVER['argv']) ? $_SERVER['argv'] : ['']);
    $config_initializer = new ConfigInitializer($repo_root, $input);
    $blt_config = $config_initializer->initialize();

    // The hostname must match the pattern local.[site-name].com, where
    // [site-name] is a value in the multisites array.
    $domain_fragments = explode('.', $http_host);
    $name = array_slice($domain_fragments, 1);
    $acsf_sites = $blt_config->get('multisites');
    if (in_array($name, $acsf_sites)) {
      $_acsf_site_name = $name;
    }
  }
}

/**
 * Include additional settings files.
 *
 * Settings are included in a very particular order to ensure that they always
 * go from the most general (default global settings) to the most specific
 * (local custom site-specific settings). Each step in the cascade also includes
 * a global (all sites) and site-specific component. The entire order is:
 *
 * 1. Acquia Cloud settings (including secret settings)
 * 2. Default general settings (provided by BLT)
 * 3. Custom general settings (provided by the project)
 * 4. Default CI settings (provided by BLT)
 * 5. Custom CI settings (provided by the project)
 * 6. Local settings (provided by the project)
 */

$settings_files = [];

// Acquia Cloud settings.
if (EnvironmentDetector::isAhEnv()) {
  $ah_group = EnvironmentDetector::getAhGroup();
  try {
    if (!EnvironmentDetector::isAcsfEnv()) {
      if ($site_dir == 'default') {
        $settings_files[] = "/var/www/site-php/$ah_group/$ah_group-settings.inc";
      }
      else {
        $settings_files[] = "/var/www/site-php/$ah_group/$site_dir-settings.inc";
      }
    }
  }
  catch (BltException $e) {
    trigger_error($e->getMessage(), E_USER_WARNING);
  }

  // Store API Keys and things outside of version control.
  // @see settings/sample-secrets.settings.php for sample code.
  // @see https://docs.acquia.com/resource/secrets/#secrets-settings-php-file
  $settings_files[] = EnvironmentDetector::getAhFilesRoot() . '/secrets.settings.php';
  $settings_files[] = EnvironmentDetector::getAhFilesRoot() . "/$site_dir/secrets.settings.php";
}

// Default global settings.
$blt_settings_files = [
  'cache',
  'config',
  'logging',
  'filesystem',
  'simplesamlphp',
  'misc',
  'drush-request-trace',
];
foreach ($blt_settings_files as $blt_settings_file) {
  $settings_files[] = __DIR__ . "/$blt_settings_file.settings.php";
}

// Custom global and site-specific settings.
$settings_files[] = DRUPAL_ROOT . '/sites/settings/global.settings.php';
$settings_files[] = DRUPAL_ROOT . "/sites/$site_dir/settings/includes.settings.php";

if (EnvironmentDetector::isCiEnv()) {
  // Default CI settings.
  $settings_files[] = __DIR__ . '/ci.settings.php';
  $settings_files[] = EnvironmentDetector::getCiSettingsFile();
  // Custom global and site-specific CI settings.
  $settings_files[] = DRUPAL_ROOT . "/sites/settings/ci.settings.php";
  $settings_files[] = DRUPAL_ROOT . "/sites/$site_dir/settings/ci.settings.php";
}

// Local global and site-specific settings.
if (EnvironmentDetector::isLocalEnv()) {
  $settings_files[] = DRUPAL_ROOT . '/sites/settings/local.settings.php';
  $settings_files[] = DRUPAL_ROOT . "/sites/$site_dir/settings/local.settings.php";
}

foreach ($settings_files as $settings_file) {
  if (file_exists($settings_file)) {
    // phpcs:ignore
    require $settings_file;
  }
}
