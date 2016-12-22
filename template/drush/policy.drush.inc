<?php

/**
 * Implements hook_drush_sitealias_alter().
 *
 * Alter alias record data in code.
 */
function policy_drush_sitealias_alter(&$alias_record) {
  if (!empty($alias_record['uri'])
    && strstr($alias_record['uri'], 'acquia') !== FALSE
    && !empty($alias_record['path-aliases']['%drush-script'])
    && $alias_record['path-aliases']['%drush-script'] == 'drush9') {
    // Acquia Cloud does not currently support drush9.
    $alias_record['path-aliases']['%drush-script'] = 'drush8';
  }
}
