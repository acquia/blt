#!/usr/bin/env php
<?php

$alias_locations = [
  "/vagrant/vendor/acquia/blt/scripts/blt/alias",
  // This is the location during "release:test" execution.
  "/var/www/blt/scripts/blt/alias",
];

foreach ($alias_locations as $alias_location) {
  if (file_exists($alias_location)) {
    $bashrc_file = "/home/vagrant/.bashrc";
    $bashrc_contents = file_get_contents($bashrc_file);
    if (!strstr($bashrc_contents, "function blt")) {
      $alias_contents = file_get_contents($alias_location);
      # Add blt alias to front of .bashrc so that it applies to non-interactive shells.
      $new_bashrc_contents = $alias_contents . $bashrc_contents;
      file_put_contents($bashrc_file, $new_bashrc_contents);
      break;
    }
  }
}
if (!isset($bashrc_file)) {
  echo "Cannot find BLT alias to install.";
  exit(1);
}
