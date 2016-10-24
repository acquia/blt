<?php

namespace Acquia\Blt;

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;

class Updater {

  /** @var \Symfony\Component\Console\Output\ConsoleOutput */
  protected $output;

  public function __construct()
  {
    $this->output = new ConsoleOutput();
    $this->output->setFormatter(new OutputFormatter(true));
  }

  public function executeUpdates($starting_version, $ending_version) {
    $updates = $this->getUpdates($starting_version, $ending_version);

    foreach ($updates as $method_name => $version) {
      $this->{$method_name}();
    }
  }

  public function getUpdates($starting_version, $ending_version) {
    $updates = [];
    $update_methods = $this->getUpdateMethods();
    foreach ($update_methods as $method_name => $version) {
      if ($version > $starting_version && $version <= $ending_version) {
        $updates[$method_name] = $version;
      }
    }

    return $updates;
  }

  /**
   *
   * @see drupal_get_schema_versions()
   */
  protected function getUpdateMethods() {
    $update_methods = [];

    // Prepare regular expression to match all possible defined hook_update_N().
    $regexp = '/^update_(?P<version>\d+)$/';
    $methods = get_class_methods(new Updates());
    // Narrow this down to functions ending with an integer, since all
    // update_N() functions end this way, and there are other
    // possible functions which may match 'update_'. We use preg_grep() here
    // instead of foreaching through all defined functions, since the loop
    // through all PHP functions can take significant execution time.
    foreach (preg_grep('/_\d+$/', $methods) as $method) {
      // If this function is a module update function, add it to the list of
      // module updates.
      if (preg_match($regexp, $method, $matches)) {
        $update_methods[$method][] = $matches['version'];
      }
    }

    sort($update_methods, SORT_NUMERIC);

    return $update_methods;
  }

  /**
   *
   */
  public function update_850() {
    $this->output->writeln('TESTING');
  }
}
