<?php

/**
 * @file
 * Bootstrap BLT.
 */

$repo_root = find_repo_root();
$classLoader = require_once $repo_root . '/vendor/autoload.php';
if (!isset($classLoader)) {
  print "Unable to find autoloader for BLT\n";
  exit(1);
}

require_once __DIR__ . '/blt-robo-run.php';

/**
 * Finds the root directory for the repository.
 *
 * Ordinarily this function is robust, but it can fail if you've symlinked BLT
 * into your vendor directory (as with a Composer path repository) and are not
 * running commands from the project root. In this state, BLT has no possible
 * way to identify the root directory.
 *
 * @return bool|string
 *   Root.
 */
function find_repo_root() {
  $possible_repo_roots = [
    getcwd(),
    realpath(__DIR__ . '/../'),
    realpath(__DIR__ . '/../../../'),
  ];
  $blt_files = ['vendor/acquia/blt', 'vendor/autoload.php'];
  // Check for PWD - some local environments will not have this key.
  if (isset($_SERVER['PWD'])) {
    array_unshift($possible_repo_roots, $_SERVER['PWD']);
  }
  foreach ($possible_repo_roots as $possible_repo_root) {
    if ($repo_root = find_directory_containing_files($possible_repo_root, $blt_files)) {
      return $repo_root;
    }
  }
  print "Unable to determine the BLT root directory.\n";
  exit(1);
}

/**
 * Traverses file system upwards in search of a given file.
 *
 * Begins searching for $file in $working_directory and climbs up directories
 * $max_height times, repeating search.
 *
 * @param string $working_directory
 *   Working directory.
 * @param array $files
 *   Files.
 * @param int $max_height
 *   Max Height.
 *
 * @return bool|string
 *   FALSE if file was not found. Otherwise, the directory path containing the
 *   file.
 */
function find_directory_containing_files($working_directory, array $files, $max_height = 10) {
  // Find the root directory of the git repository containing BLT.
  // We traverse the file tree upwards $max_height times until we find $files.
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
 *   Dir.
 * @param array $files
 *   Files.
 *
 * @return bool
 *   Exists.
 */
function files_exist($dir, array $files) {
  foreach ($files as $file) {
    if (!file_exists($dir . '/' . $file)) {
      return FALSE;
    }
  }
  return TRUE;
}
