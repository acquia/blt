<?php

namespace Drupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {

  }

  /**
   * The BeforeSuite hook is run before any feature in the suite runs.
   *
   * @BeforeSuite
   */
  public static function prepare($event) {
    if (db_table_exists('watchdog')) {
      db_truncate('watchdog')->execute();
    }
  }

  /**
   * The AfterScenario hook is run after executing a scenario.
   *
   * @AfterScenario
   */
  public function afterScenario($event) {
    if (db_table_exists('watchdog')) {
      $log = db_select('watchdog', 'w')
        ->fields('w')
        ->condition('w.type', 'php', '=')
        ->execute()
        ->fetchAll();
      if (!empty($log)) {
        foreach ($log as $error) {
          // Make the substitutions easier to read in the log.
          $error->variables = unserialize($error->variables);
          print_r($error);
        }
        throw new \Exception('PHP errors logged to watchdog in this scenario.');
      }
    }
  }

}
