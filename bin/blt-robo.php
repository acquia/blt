<?php

/**
 * @file
 */

$repo_root = find_repo_root();
$autoload = require_once $repo_root . '/vendor/autoload.php';
if (!isset($autoload)) {
  print "Unable to find autoloader for BLT\n";
  exit(1);
}

require_once __DIR__ . '/blt-robo-run.php';

/**
 * Finds the root directory for the repository.
 *
 * @return bool|string
 */
function find_repo_root() {
  $possible_repo_roots = [
    $_SERVER['PWD'],
    getcwd(),
    realpath(__DIR__ . '/../'),
    realpath(__DIR__ . '/../../../'),
  ];
  foreach ($possible_repo_roots as $possible_repo_root) {
    if ($repo_root = find_directory_containing_files($possible_repo_root, ['vendor/bin/blt', 'vendor/autoload.php'])) {
      return $repo_root;
    }
  }
}

/**
 * Traverses file system upwards in search of a given file.
 *
 * Begins searching for $file in $working_directory and climbs up directories
 * $max_height times, repeating search.
 *
 * @param string $working_directory
 * @param array $files
 * @param int $max_height
 *
 * @return bool|string
 *   FALSE if file was not found. Otherwise, the directory path containing the
 *   file.
 */
function find_directory_containing_files($working_directory, $files, $max_height = 10) {
  // Find the root directory of the git repository containing BLT.
  // We traverse the file tree upwards $max_height times until we find
  // vendor/bin/blt.
  $file_path = $working_directory;
  for ($i = 0; $i <= $max_height; $i++) {
    if (files_exist($file_path, $files)) {
      return $file_path;
    }
    else {
      $file_path = realpath($file_path . '/..');
    }
  }

  return FALSE;
}

/**
 * Determines if an array of files exist in a particular directory.
 *
 * @param string $dir
 * @param array $files
 *
 * @return bool
 */
function files_exist($dir, $files) {
  foreach ($files as $file) {
    if (!file_exists($dir . '/' . $file)) {
      return FALSE;
    }
  }
  return TRUE;
}
