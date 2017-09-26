<?php

/**
 * @file
 * Display all errors for all but tests and prod envs.
 */

if ($is_local_env || $is_dev_env) {
  // Ultimately, EVERY compiler message represents a mistake in the code.
  // Acquia Cloud isn't quite ready for E_STRICT yet.
  error_reporting(E_ALL);
  // Print errors on WSOD.
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
}
