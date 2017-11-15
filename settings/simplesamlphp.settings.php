<?php

/**
 * @file
 * SimpleSAMLphp configuration
 */

// Provide universal absolute path to the installation.
if (isset($_ENV['AH_SITE_NAME']) && is_dir('/var/www/html/' . $_ENV['AH_SITE_NAME'] . '/vendor/simplesamlphp/simplesamlphp')) {
  $settings['simplesamlphp_dir'] = '/var/www/html/' . $_ENV['AH_SITE_NAME'] . '/vendor/simplesamlphp/simplesamlphp';

  // Force server port to 443 with HTTPS environments when behind a load
  // balancer which is a requirement for SimpleSAML with ADFS when providing a
  // redirect path.
  // @see https://github.com/simplesamlphp/simplesamlphp/issues/450
  if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] === 'on') {
    $_SERVER['SERVER_PORT'] = 443;
  }
}
else {
  // Local SAML path.
  if (is_dir(DRUPAL_ROOT . '/../simplesamlphp') &&
    is_dir(DRUPAL_ROOT . '/../vendor/simplesamlphp/simplesamlphp')) {
    $settings['simplesamlphp_dir'] = DRUPAL_ROOT . '/../vendor/simplesamlphp/simplesamlphp';
  }
}
