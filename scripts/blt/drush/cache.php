<?php

/**
 * @file
 * Creates a unique temporary cache directory given a site, env, and uri.
 *
 * This is used for every blt command invocation on site install and update.
 */

$site = $argv[1];
$env = $argv[2];
$uri = $argv[3];


if (empty($argv[3])) {
  echo "Warning, Site URI not identified. Ensure ACSF module is enabled.\n";
  echo "Confirm that the module is enabled via configuration. This may live in: \n";
  echo "config/default (core.extensions.yml) and/or specific config splits.\n";
  exit(1);
}

// Create a temporary cache directory for this drush process only.
$cache_directory = sprintf('/mnt/tmp/%s.%s/drush_tmp_cache/%s', $site, $env, md5($uri));
// phpcs:ignore
shell_exec(sprintf('mkdir -p %s', escapeshellarg($cache_directory)));

if (!file_exists($cache_directory)) {
  syslog(LOG_ERR, sprintf('Drush updates could not be executed, as the required cache directory [%s] is missing.', $cache_directory));
  die('Missing or corrupted drush cache for this process.');
}
else {
  echo "$cache_directory";
}
