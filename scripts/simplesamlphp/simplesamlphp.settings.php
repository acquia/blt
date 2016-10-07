<?php

// SimpleSAMLphp configuration
# Provide universal absolute path to the installation.
if (isset($_ENV['AH_SITE_NAME']) && is_dir('/var/www/html/' . $_ENV['AH_SITE_NAME'] . '/vendor/simplesamlphp/simplesamlphp')) {
  $settings['simplesamlphp_dir'] = '/var/www/html/' . $_ENV['AH_SITE_NAME'] . '/vendor/simplesamlphp/simplesamlphp';
}
else {
  // Local SAML path.
  if (is_dir(DRUPAL_ROOT . '/../simplesamlphp')) {
    $settings['simplesamlphp_dir'] = DRUPAL_ROOT . '/../vendor/simplesamlphp/simplesamlphp';
  }
}
