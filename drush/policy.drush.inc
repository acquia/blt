<?php

/**
 * Implements hook_drush_sitealias_alter().
 *
 * Alter alias record data in code.
 */
function policy_drush_sitealias_alter(&$alias_record) {
  if (!empty($alias_record['remote-host'])) {
    // Set Drush version correctly on Acquia Cloud.
    if (!empty($alias_record['uri'])
      && strstr($alias_record['remote-host'], 'acquia') !== FALSE
      && !empty($alias_record['path-aliases']['%drush-script'])
      && $alias_record['path-aliases']['%drush-script'] == 'drush9'
    ) {
      // Acquia Cloud does not currently support drush9.
      $alias_record['path-aliases']['%drush-script'] = 'drush8';
    }

    // If the alias is "remote", but the remote site is
    // the system this command is running on, convert the
    // alias record to a local alias.
    $uname = php_uname('n');
    if ($alias_record['remote-host'] == $uname) {
      unset($alias_record['remote-host']);
      unset($alias_record['remote-user']);
    }
  }
}
