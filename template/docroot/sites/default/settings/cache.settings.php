<?php

/**
 * @file
 * Contains caching configuration.
 */

if ($is_ah_env) {
  switch ($ah_env) {
    case 'prod':
      $config['system.logging']['error_level'] = 'hide';
      break;
  }
}
