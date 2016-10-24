<?php

namespace Acquia\Blt;

use Acquia\Blt\Annotations\Update;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\IndexedReader;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;

class Updater {

  /** @var \Symfony\Component\Console\Output\ConsoleOutput */
  protected $output;

  public function __construct()
  {
    $this->output = new ConsoleOutput();
    $this->output->setFormatter(new OutputFormatter(true));
    AnnotationRegistry::registerFile(__DIR__ . '/Annotations/Update.php');
    $this->annotationsReader = new IndexedReader(new AnnotationReader());
  }

  public function executeUpdates($starting_version, $ending_version) {
    $updates = $this->getUpdates($starting_version, $ending_version);

    /**
     * @var string $method_name
     * @var Update $update
     */
    foreach ($updates as $method_name => $update) {
      $this->output->writeln("Executing Updater->$method_name:");
      $this->output->writeln("  {$update->description}");
      $this->{$method_name}();
    }
  }

  public function getUpdates($starting_version, $ending_version) {
    $updates = [];
    $update_methods = $this->getUpdateMethods();
    foreach ($update_methods as $method_name => $version) {
      $reflectionMethod = new \ReflectionMethod($this, $method_name);
      $annotations = $this->annotationsReader->getMethodAnnotations($reflectionMethod);
      /** @var Update $update_annotation */
      $update_annotation = $annotations['Acquia\Blt\Annotations\Update'];
      $version = $update_annotation->version;

      if ($version > $starting_version && $version <= $ending_version) {
        $updates[$method_name] = $update_annotation;
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
    $methods = get_class_methods($this);
    // Narrow this down to functions ending with an integer, since all
    // update_N() functions end this way, and there are other
    // possible functions which may match 'update_'. We use preg_grep() here
    // instead of foreaching through all defined functions, since the loop
    // through all PHP functions can take significant execution time.
    foreach (preg_grep('/_\d+$/', $methods) as $method) {
      // If this function is a module update function, add it to the list of
      // module updates.
      if (preg_match($regexp, $method, $matches)) {
        $update_methods[$method] = $matches['version'];
      }
    }

    asort($update_methods, SORT_NUMERIC);

    return $update_methods;
  }

  /**
   * @Update(
   *   version = "8.5.0",
   *   description = "Re-provisioning VM."
   * )
   */
  public function update_850() {
    // Do nothing.
  }
}
