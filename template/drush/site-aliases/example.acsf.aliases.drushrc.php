<?php

// Add a site ID and configure the sites and environment names to
// create an alias file for an Acquia Cloud Site Factory site.

// Acquia Cloud Site Factory id.
$site_id = '[PROJECT-NAME]';

// List of sites. For each site, one record should be added.
$sites = array(
  'demo',
);

// Configure the server used for the production environment.
$prod_web = 'web-###';

// Configure the server used for the dev and test environments.
$dev_web = 'staging-###';

// =======================END OF CONFIGURATION==============================.

if ($site_id !== '[PROJECT-NAME]') {

  // Acquia Cloud Site Factory environment settings.
  $acsf_prod = array(
    'remote-user' => $site_id . '.01live',
    'root' => '/var/www/html/' . $site_id . '.01live/docroot',
    'remote-host' => $prod_web . '.enterprise-g1.hosting.acquia.com',
  );

  $acsf_stage = array(
    'remote-user' => $site_id . '.01test',
    'root' => '/var/www/html/' . $site_id . '.01test/docroot',
    'remote-host' => $dev_web . '.enterprise-g1.hosting.acquia.com',
  );

  $acsf_dev = array(
    'remote-user' => $site_id . '.01dev',
    'root' => '/var/www/html/' . $site_id . '.01dev/docroot',
    'remote-host' => $dev_web . '.enterprise-g1.hosting.acquia.com',
  );

  // These defaults connect to the Acquia Cloud Site Factory.
  $acsf_defaults = array(
    'ssh-options' => '-p 22',
    'path-aliases' => array(
      '%dump-dir' => '/mnt/tmp/'
    )
  );

  // Create the aliases using the defaults and the list of sites.
  foreach ($sites as $site) {
    $aliases[$site . '.dev'] = array_merge(
      $acsf_defaults,
      $acsf_dev,
      array(
        'uri' => $site . '.dev-' . $site_id . '.acsitefactory.com',
      )
    );

    $aliases[$site . '.stage'] = array_merge(
      $acsf_defaults,
      $acsf_dev,
      array(
        'uri' => $site . '.test-' . $site_id . '.acsitefactory.com',
      )
    );

    $aliases[$site . '.prod'] = array_merge(
      $acsf_defaults,
      $acsf_prod,
      array(
        'uri' => $site . '.' . $site_id . '.acsitefactory.com',
      )
    );
  }
}
