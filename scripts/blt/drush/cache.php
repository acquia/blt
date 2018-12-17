<?php

/**
 * @file
 * Creates a unique temporary cache directory given a site, env, and uri.
 * This is used for every blt command invocation on site install and update.
 */

$site = $argv[1];
$env = $argv[2];
$uri = $argv[3];


if (empty($argv[3])) {
  echo "Error: Not enough arguments. Site, environment, and uri are required.\n";
  exit(1);
}

// Create a temporary cache directory for this drush process only.
$cache_directory = sprintf('/mnt/tmp/%s.%s/drush_tmp_cache/%s', $site, $env, md5($uri));
shell_exec(sprintf('mkdir -p %s', escapeshellarg($cache_directory)));

if (!file_exists($cache_directory)) {
  syslog(LOG_ERR, sprintf('Drush updates could not be executed, as the required cache directory [%s] is missing.', $cache_directory));
  die('Missing or corrupted drush cache for this process.');
}
else {
  echo "$cache_directory";
}