<?php

/**
 * @file
 * SimpleSamlPhp Acquia Configuration.
 *
 * This file was last modified on Nov 4, 2015.
 *
 * All custom changes below. Modify as needed.
 */

/**
 * Defines Acquia account specific options in $ah_options keys.
 *
 *   - 'database_name': Should be the Acquia Cloud workflow database name which
 *     will store SAML session information.set
 *     You can use any database that you have defined in your workflow.
 *     Use the database "role" without the stage ("dev", "stage", "test", etc.)
 *   - 'session_store': Define the session storage service to use in each
 *     Acquia environment ("memcache" or "database").
 */

// Set some security and other configs that are set above, however we
// overwrite them here to keep all changes in one area.
$config['technicalcontact_name'] = "Your Name";
$config['technicalcontact_email'] = "your_email@yourdomain.com";

// Change these for your installation.
$config['secretsalt'] = 'y0h9d13pki9qdhfm3l5nws4jjn55j6hj';
$config['auth.adminpassword'] = 'mysupersecret';

$_SERVER['SERVER_PORT'] = 443;
$_SERVER['HTTPS'] = 'true';
$protocol = 'https://';
$port = ':' . $_SERVER['SERVER_PORT'];


/**
 * Multi-site installs.
 *
 * Support multi-site installations at different base URLs.
 */
// $config['baseurlpath'] = "https://{$_SERVER['SERVER_NAME']}/simplesaml/";
$config['baseurlpath'] = $protocol . $_SERVER['HTTP_HOST'] . $port . '/simplesaml/';
/**
 * Cookies No Cache.
 *
 * Allow users to be automatically logged in if they signed in via the same
 * SAML provider on another site.
 *
 * Warning: This has performance implications for anonymous users.
 *
 * @link https://docs.acquia.com/articles/using-simplesamlphp-acquia-cloud-site
 */
// setcookie('NO_CACHE', '1');.
if (!getenv('AH_SITE_ENVIRONMENT')) {
  // Add your local configuration here.
  // Local Development.
  $config['store.type'] = 'sql';
  $config['store.sql.dsn'] = sprintf('mysql:host=%s;port=%s;dbname=%s', '127.0.0.1', '', 'drupal');
  $config['store.sql.username'] = 'drupal';
  $config['store.sql.password'] = 'drupal';
  $config['store.sql.prefix'] = 'simplesaml';
  $config['certdir'] = "/var/www/simplesamlphp/cert/";
  $config['metadatadir'] = "/var/www/simplesamlphp/metadata";
  $config['baseurlpath'] = 'simplesaml/';
  $config['loggingdir'] = '/var/www/simplesamlphp/log/';
}
}
elseif (file_exists("/mnt/files/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/files-private/sites.json")) {
  // On ACSF.
  $config['certdir'] = "/mnt/www/html/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/simplesamlphp/cert/";
  $config['metadatadir'] = "/mnt/www/html/{$_ENV['AH_SITE_GROUP']}.{$_ENV['AH_SITE_ENVIRONMENT']}/simplesamlphp/metadata";
  $config['baseurlpath'] = 'simplesaml/';
  $config['logging.handler'] = 'file';
  $config['loggingdir'] = dirname(getenv('ACQUIA_HOSTING_DRUPAL_LOG'));
  // Setup basic.
  $config['logging.logfile'] = 'simplesamlphp-' . date('Ymd') . '.log';
  $creds_json = file_get_contents('/var/www/site-php/' . $_ENV['AH_SITE_GROUP'] . '.' . $_ENV['AH_SITE_ENVIRONMENT'] . '/creds.json');
  $databases = json_decode($creds_json, TRUE);
  $creds = $databases['databases'][$_ENV['AH_SITE_GROUP']];
  require_once "/usr/share/php/Net/DNS2_wrapper.php";
  try {
    $resolver = new Net_DNS2_Resolver(array(
      'nameservers' => array(
        '127.0.0.1',
        'dns-master',
      ),
    ));
    $response = $resolver->query("cluster-{$creds['db_cluster_id']}.mysql", 'CNAME');
    $creds['host'] = $response->answer[0]->cname;
  }
  catch (Net_DNS2_Exception $e) {
    $creds['host'] = "";
  }
  $config['store.type'] = 'sql';
  $config['store.sql.dsn'] = sprintf('mysql:host=%s;port=%s;dbname=%s', $creds['host'], $creds['port'], $creds['name']);
  $config['store.sql.username'] = $creds['user'];
  $config['store.sql.password'] = $creds['pass'];
  $config['store.sql.prefix'] = 'simplesaml';
}
}
}
