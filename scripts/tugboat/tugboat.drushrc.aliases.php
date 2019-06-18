<?php

/**
 * @file
 * Tugboat aliases.
 */

$aliases['${project.machine_name}.ci'] = array(
  'uri' => $_ENV['TUGBOAT_URL'],
  'root' => '/var/lib/tugboat/docroot',
);
