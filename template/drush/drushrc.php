<?php

// Load a drushrc.php configuration file from the current working directory.
$options['config'][] = realpath(__DIR__ . '/../vendor/acquia/blt/drush/drushrc.php');

if (file_exists(__DIR__ . '/../docroot/sites/default/local.drushrc.php')) {
  require __DIR__ . '/../docroot/sites/default/local.drushrc.php';
}

// Add project-specific drush configuration below.
// @see https://github.com/acquia/blt/tree/8.x/drush/drushrc.php For examples
// of valid statements for a Drush runtime config (drushrc) file.
