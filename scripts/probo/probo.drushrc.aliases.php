<?php

$aliases['${project.machine_name}.ci'] = array(
  'uri' => $_ENV['PROBO_ENVIRONMENT'],
  'root' => $_ENV['SRC_DIR'] . '/docroot',
);
