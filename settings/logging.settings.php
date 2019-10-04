<?php

/**
 * @file
 * Display all errors for all but tests and prod envs.
 */

use Acquia\Blt\Robo\Common\EnvironmentDetector;

// Prevent errors from showing in the UI for prod & qa environments.
if (EnvironmentDetector::isProdEnv() || EnvironmentDetector::isStageEnv()) {
  $config['system.logging']['error_level'] = 'hide';
}

if (EnvironmentDetector::isLocalEnv() || EnvironmentDetector::isDevEnv()) {
  // Ultimately, EVERY compiler message represents a mistake in the code.
  // Acquia Cloud isn't quite ready for E_STRICT yet.
  error_reporting(E_ALL);
  // Print errors on WSOD.
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
}
