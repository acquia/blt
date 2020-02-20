<?php

/**
 * @file
 * Probo aliases.
 */

$aliases['${project.machine_name}.ci'] = [
  'uri' => 'http://localhost',
  'root' => getenv('SRC_DIR') . '/docroot',
];
