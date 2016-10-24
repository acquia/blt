<?php

namespace Acquia\Blt\Update;

use Acquia\Blt\Annotations\Update;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\IndexedReader;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;

class Updater {

  /** @var \Symfony\Component\Console\Output\ConsoleOutput */
  protected $output;

  public function __construct($update_class = 'Acquia\Blt\Update\Updates')
  {
    $this->output = new ConsoleOutput();
    $this->output->setFormatter(new OutputFormatter(true));
    AnnotationRegistry::registerFile(__DIR__ . '/../Annotations/Update.php');
    $this->annotationsReader = new IndexedReader(new AnnotationReader());
    $this->updateClass = $update_class;
  }

  public function executeUpdates($updates) {
    /**
     * @var string $method_name
     * @var Update $update
     */
    foreach ($updates as $method_name => $update) {
      $this->output->writeln("Executing Updater->$method_name: {$update->description}");
      call_user_func([$this->updateClass, $method_name]);
    }
  }

  public function printUpdates($updates) {
    /**
     * @var string $method_name
     * @var Update $update
     */
    foreach ($updates as $method_name => $update) {
      $this->output->writeln("{$update->version}: {$update->description}");
    }
  }

  public function getUpdates($starting_version, $ending_version) {
    $updates = [];
    $update_methods = $this->getAllUpdateMethods();
    /**
     * @var string $method_name
     * @var Update $metadata
     */
    foreach ($update_methods as $method_name => $metadata) {
      $version = $metadata->version;

      if ($version > $starting_version && $version <= $ending_version) {
        $updates[$method_name] = $metadata;
      }
    }

    return $updates;
  }

  /**
   *
   * @see drupal_get_schema_versions()
   */
  protected function getAllUpdateMethods() {
    $update_methods = [];
    $methods = get_class_methods($this->updateClass);
    foreach ($methods as $method_name) {
      $reflectionMethod = new \ReflectionMethod($this->updateClass, $method_name);
      $annotations = $this->annotationsReader->getMethodAnnotation($reflectionMethod, 'Acquia\Blt\Annotations\Update');
      if ($annotations) {
        $update_methods[$method_name] = $annotations;
      }
    }

    return $update_methods;
  }
}
