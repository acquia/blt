<?php

/**
 * @file
 * Custom drush commands provided by Bolt.
 */

/**
 * Implements hook_drush_command().
 *
 * In this hook, you specify which commands your
 * drush module makes available, what it does and
 * description.
 *
 * Notice how this structure closely resembles how
 * you define menu hooks.
 *
 * See `drush topic docs-commands` for a list of recognized keys.
 */
function blt_drush_command() {
  $items = array();

  $items['blt-doctor'] = array(
    'description' => "Check local settings and configuration to ensure that things are set up properly.",
    'aliases' => array('bdr'),
    'bootstrap' => DRUSH_BOOTSTRAP_MAX,
  );

  return $items;
}

/**
 * Callback for `blt-doctor` command.
 */
function drush_blt_doctor() {
  require_once __DIR__ . '/../src/Drush/Command/BltDoctorCommand.php';
  $blt_doctor = new \Acquia\Blt\Drush\Command\BltDoctor();
  $blt_doctor->checkAll();
}
