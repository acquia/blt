<?php

/**
 * @file
 * Factory Hook: post-install.
 *
 * This hook enables you to execute PHP after a new website is created
 * in your subscription. Unlike most API-based hooks, this hook does not
 * take arguments, but instead executes the PHP code it is provided.
 *
 * This is used so that an ACSF site install is identical to the local BLT site
 * install, with the environment, site, and uri CLI runtime arguments overriding
 * all other configuration.
 *
 * phpcs:disable DrupalPractice.CodeAnalysis.VariableAnalysis
 */

// Acquia hosting site / environment names.
$site = getenv('AH_SITE_GROUP');
$env = getenv('AH_SITE_ENVIRONMENT');
$uri = FALSE;

global $_acsf_site_name;

// ACSF Database Role.
if (!empty($GLOBALS['gardens_site_settings']['conf']['acsf_db_name'])) {
  $db_role = $GLOBALS['gardens_site_settings']['conf']['acsf_db_name'];
}

$docroot = sprintf('/var/www/html/%s.%s/docroot', $site, $env);

// BLT executable.
$blt = sprintf('/var/www/html/%s.%s/vendor/bin/blt', $site, $env);

/**
 * Exit on error.
 *
 * @param string $message
 *   A message to write to sdderr.
 */
function error($message) {
  fwrite(STDERR, $message);
  exit(1);
}

fwrite(STDERR, sprintf("Running updates on: site: %s; env: %s; db_role: %s; name: %s;\n", $site, $env, $db_role, $_acsf_site_name));

include_once $docroot . '/sites/g/sites.inc';
$sites_json = gardens_site_data_load_file();
if (!$sites_json) {
  error('The ACSF site registry could not be loaded from the server.');
}

foreach ($sites_json['sites'] as $site_domain => $site_info) {
  if ($site_info['conf']['acsf_db_name'] === $db_role && !empty($site_info['flags']['preferred_domain'])) {
    $uri = $site_domain;
    fwrite(STDERR, "Site domain: $uri;\n");
    break;
  }
}
if (!$uri) {
  error('Could not find the preferred domain that belongs to the site.');
}

$docroot = sprintf('/var/www/html/%s.%s/docroot', $site, $env);

// phpcs:ignore
$cache_directory = exec("/usr/bin/env php /mnt/www/html/$site.$env/vendor/acquia/blt/scripts/blt/drush/cache.php $site $env $uri");
// phpcs:ignore
shell_exec(sprintf('mkdir -p %s', escapeshellarg($cache_directory)));

// Execute the updates.
$command = sprintf(
  'DRUSH_PATHS_CACHE_DIRECTORY=%s %s drupal:update --environment=%s --site=%s --define drush.uri=%s --verbose --no-interaction',
  escapeshellarg($cache_directory),
  escapeshellarg($blt),
  escapeshellarg($env),
  escapeshellarg($_acsf_site_name),
  escapeshellarg($uri)
);
fwrite(STDERR, "Executing: $command with cache dir $cache_directory;\n");

$result = 0;
$output = [];
// phpcs:ignore
exec($command, $output, $result);
print implode("\n", $output);

// Clean up the drush cache directory.
// phpcs:ignore
shell_exec(sprintf('rm -rf %s', escapeshellarg($cache_directory)));

if ($result) {
  fwrite(STDERR, "Command execution returned status code: $result!\n");
  exit($result);
}
