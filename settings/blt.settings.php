<?php

/**
 * @file
 * Setup BLT utility variables, include required files.
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;
use Acquia\Blt\Robo\Exceptions\BltException;
use Acquia\DrupalEnvironmentDetector\FilePaths;

/**
 * Detect environments, sites, and hostnames.
 */

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

/**
 * Site path.
 *
 * @var string $site_path
 * This is always set and exposed by the Drupal Kernel.
 */
// phpcs:ignore
$site_name = EnvironmentDetector::getSiteName($site_path);
// Acquia Cloud settings.
if (EnvironmentDetector::isAhEnv()) {
  try {
    if (!EnvironmentDetector::isAcsfEnv()) {
      $settings_files[] = FilePaths::ahSettingsFile(EnvironmentDetector::getAhGroup(), $site_name);
    }
  }
  catch (BltException $e) {
    trigger_error($e->getMessage(), E_USER_WARNING);
  }

  // Store API Keys and things outside of version control.
  // @see settings/sample-secrets.settings.php for sample code.
  // @see https://docs.acquia.com/resource/secrets/#secrets-settings-php-file
  $settings_files[] = EnvironmentDetector::getAhFilesRoot() . '/secrets.settings.php';
  $settings_files[] = EnvironmentDetector::getAhFilesRoot() . "/$site_name/secrets.settings.php";
}

// Default global settings.
$blt_settings_files = [
  'cache',
  'config',
  'logging',
  'filesystem',
  'simplesamlphp',
  'misc',
];
foreach ($blt_settings_files as $blt_settings_file) {
  $settings_files[] = __DIR__ . "/$blt_settings_file.settings.php";
}

// Custom global and site-specific settings.
$settings_files[] = DRUPAL_ROOT . '/sites/settings/global.settings.php';
$settings_files[] = DRUPAL_ROOT . "/sites/$site_name/settings/includes.settings.php";

if (EnvironmentDetector::isCiEnv()) {
  // Default CI settings.
  $settings_files[] = __DIR__ . '/ci.settings.php';
  $settings_files[] = EnvironmentDetector::getCiSettingsFile();
  // Custom global and site-specific CI settings.
  $settings_files[] = DRUPAL_ROOT . "/sites/settings/ci.settings.php";
  $settings_files[] = DRUPAL_ROOT . "/sites/$site_name/settings/ci.settings.php";
}

// Local global and site-specific settings.
if (EnvironmentDetector::isLocalEnv()) {
  $settings_files[] = DRUPAL_ROOT . '/sites/settings/local.settings.php';
  $settings_files[] = DRUPAL_ROOT . "/sites/$site_name/settings/local.settings.php";
}

foreach ($settings_files as $settings_file) {
  if (file_exists($settings_file)) {
    // phpcs:ignore
    require $settings_file;
  }
}
