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

/**
 * Indents a key-value row.
 *
 * @param mixed $input
 *   An array of key-value rows, or a string.
 *
 * @param $metadata
 *   The formatting metadata.
 *
 * @return array|string
 *   The prefixed output.
 */
function drush_blt_indent_filter($input, $metadata) {
  $indent = !empty($metadata['indent']) ? $metadata['indent'] : 2;
  $prefix = str_repeat(' ', $indent);
  if (is_array($input)) {
    foreach ($input as $key => $value) {
      $input[$prefix . $key] = $value;
      unset($input[$key]);
    }
  }
  else {
    $input = $prefix . $input;
  }

  return $input;
}

/**
 * Prints selected rows from the status table.
 *
 * @param array $status_table
 *   The status table returned by drush_core_status().
 *
 * @param array $keys
 *   An array of keys for the rows to print.
 */
function drush_blt_print_status_rows($status_table, $keys) {
  $intersection = array_intersect_key($status_table, array_flip($keys));
  $output = drush_format($intersection, array(
    'format' => 'key-value',
    'formatted-filter' => array(
      'drush_blt_indent_filter',
    ),
    'indent' => 2,
  ));
  drush_print($output);
}
