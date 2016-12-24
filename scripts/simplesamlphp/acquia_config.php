<?php
// All custom changes below. Modify as needed.
// Defines account specific settings.
// $ah_options['database_name'] should be the Acquia Cloud workflow database name which
// will store SAML session information.set
// You can use any database that you have defined in your workflow.
// Use the database "role" without the stage ("dev", "stage", or "test", etc.)
//This file was last modified on Nov 4, 2015.
$ah_options = array(
  'database_name' => 'mydatabasename',
  'session_store' => array(
    'prod' => 'memcache',
    'test' => 'memcache',
    'dev'  => 'database',
  ),
);
// Set some security and other configs that are set above, however we
// overwrite them here to keep all changes in one area
$config['technicalcontact_name'] = "Your Name";
$config['technicalcontact_email'] = "your_email@yourdomain.com";
// Change these for your installation
$config['secretsalt'] = 'y0h9d13pki9qdhfm3l5nws4jjn55j6hj';
$config['auth.adminpassword'] = 'mysupersecret';
// Prevent Varnish from interfering with SimpleSAMLphp.
setcookie('NO_CACHE', '1');
if (empty($_ENV['AH_SITE_ENVIRONMENT'])) {
  // add any local configuration here
} else {
  $ah_options['env'] = $_ENV['AH_SITE_ENVIRONMENT'];
  $config = acquia_logging_config($config);
  $config = acquia_session_store_config($config, $ah_options);
}
function acquia_session_store_config($config, $ah_options) {
  if ($ah_options['session_store'][$ah_options['env']] == 'memcache') {
    $config = mc_session_store($config);
  } elseif ($ah_options['session_store'][$ah_options['env']] == 'database') {
    $config = sql_session_store($config, $ah_options['database_name']);
  }
  return $config;
}
function acquia_logging_config($config) {
  // Set log location, as specified by Acquia
  $config['logging.handler'] = 'file';
  $config['loggingdir'] = dirname($_ENV['ACQUIA_HOSTING_DRUPAL_LOG']);
  $config['logging.logfile'] = 'simplesamlphp-' . date("Ymd") . '.log';
  return $config;
}
function mc_session_store($config) {
  $config['store.type'] = 'memcache';
  $config['memcache_store.servers'] = mc_info();
  return $config;
}
function mc_info() {
  $creds_json = file_get_contents('/var/www/site-php/' . $_ENV['AH_SITE_NAME'] . '/creds.json');
  $creds = json_decode($creds_json, TRUE);
  $mc_server = array();
  $mc_pool = array();
  foreach ($creds['memcached_servers'] as $fqdn) {
    $mc_server['hostname'] = preg_replace('/:.*?$/', '', $fqdn);
    array_push($mc_pool, $mc_server);
  }
  return array($mc_pool);
}
function sql_session_store($config, $database_name) {
  $creds = db_info($database_name);
  $config['store.type'] = 'sql';
  $config['store.sql.dsn'] = sprintf('mysql:host=%s;port=%s;dbname=%s', $creds['host'], $creds['port'], $creds['name']);
  $config['store.sql.username'] = $creds['user'];
  $config['store.sql.password'] = $creds['pass'];
  $config['store.sql.prefix'] = 'simplesaml';
  return $config;
}
function db_info($db_name) {
  $creds_json = file_get_contents('/var/www/site-php/' . $_ENV['AH_SITE_NAME'] . '/creds.json');
  $databases = json_decode($creds_json, TRUE);
  $db = $databases['databases'][$db_name];
  $db['host'] = ($host = ah_db_current_host($db['db_cluster_id'])) ? $host : key($db['db_url_ha']);
  return $db;
}
function ah_db_current_host($db_cluster_id) {
  require_once("/usr/share/php/Net/DNS2_wrapper.php");
  try {
    $resolver = new Net_DNS2_Resolver(array('nameservers' => array('127.0.0.1', 'dns-master')));
    $response = $resolver->query("cluster-{$db_cluster_id}.mysql", 'CNAME');
    $cached_id = $response->answer[0]->cname;
  }
  catch (Net_DNS2_Exception $e) {
    $cached_id = "";
  }
  return $cached_id;
}
