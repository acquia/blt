<?php

/**
 * @file
 * ACSF pre-settings-php hook.
 *
 * @see https://docs.acquia.com/site-factory/tiers/paas/workflow/hooks
 *
 * phpcs:disable DrupalPractice.CodeAnalysis.VariableAnalysis
 */

// Configure your hash salt here.
// $settings['hash_salt'] = '';.
require DRUPAL_ROOT . '/../vendor/acquia/blt/settings/blt.settings.php';
