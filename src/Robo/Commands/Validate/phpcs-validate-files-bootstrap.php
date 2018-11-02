<?php

/**
 * @file
 * This bootstrap file should be used only with tests:phpcs:sniff:files.
 *
 * Its purpose is to take a list of files specified for sniffing, and filter it
 * according to the file patterns established in phpcs.xml. By default, phpcs
 * will ignore any file patterns defined in phpcs.xml when a list of files is
 * specified.
 *
 * "If you have asked PHP_CodeSniffer to check a specific file rather than an
 * entire directory, the extension of the specified file will be ignored."
 *
 * @see https://github.com/acquia/blt/pull/2126
 * @see https://github.com/acquia/blt/issues/2129
 * @see https://github.com/squizlabs/PHP_CodeSniffer/wiki/Advanced-Usage#specifying-valid-file-extensions
 */

// Unset the list of specified files.
$cli = $phpcs->cli;
$specified_files = $cli->values['files'];
$cli->values['files'] = [];

// Re-initialize standard to get a list of files and directories that would be
// scanned by default.
$phpcs = new PHP_CodeSniffer($values['verbosity'], NULL, NULL, NULL);
$phpcs->setCli($cli);
$phpcs->initStandard($standard, $values['sniffs'], $values['exclude']);
$values = $this->values;

// Compute the intersection of the specified files in the default files.
$intersection = [];
foreach ($values['files'] as $file) {
  foreach ($specified_files as $specified_file) {
    // Scan the specified file if a portion of its path matches one of the
    // default files or directories.
    if (strpos($specified_file, $file) !== FALSE) {
      $intersection[] = $specified_file;
    }
  }
}

// Overwrite the files list using the computed intersection.
$values['files'] = $intersection;

// Return early to prevent hang in stream_get_contents().
if (empty($values['files'])) {
  exit(0);
}
