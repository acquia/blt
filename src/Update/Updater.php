<?php

namespace Acquia\Blt\Update;

use Acquia\Blt\Annotations\Update;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\IndexedReader;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use vierbergenlars\SemVer\version;

class Updater {

  /** @var \Symfony\Component\Console\Output\ConsoleOutput */
  protected $output;

  /** @var string */
  protected $repoRoot;

  /** @var \Symfony\Component\Filesystem\Filesystem  */
  protected $fs;

  /**
   * @return string
   */
  public function getRepoRoot() {
    return $this->repoRoot;
  }

  /**
   * @param string $repoRoot
   */
  public function setRepoRoot($repoRoot) {
    if (!$this->fs->exists($repoRoot)) {
      throw new FileNotFoundException();
    }

    $this->repoRoot = $repoRoot;
  }

  public function __construct($update_class = 'Acquia\Blt\Update\Updates')
  {
    $this->output = new ConsoleOutput();
    $this->output->setFormatter(new OutputFormatter(true));
    AnnotationRegistry::registerFile(__DIR__ . '/../Annotations/Update.php');
    $this->annotationsReader = new IndexedReader(new AnnotationReader());
    $this->updateClassName = $update_class;
    $this->fs = new Filesystem();
  }

  /**
   * @param $updates \Acquia\Blt\Annotations\Update[]
   */
  public function executeUpdates($updates) {
    /** @var Updates $updates_object */
    $updates_object = new $this->updateClassName();
    $updates_object->setUpdater($this);
    /**
     * @var string $method_name
     * @var Update $update
     */
    foreach ($updates as $method_name => $update) {
      $this->output->writeln("Executing Updater->$method_name: {$update->description}");
      call_user_func([$updates_object, $method_name]);
    }
  }

  /**
   * @param $updates \Acquia\Blt\Annotations\Update[]
   */
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

      if (version::gt($version, $starting_version) && version::lte($version, $ending_version)) {
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
    $methods = get_class_methods($this->updateClassName);
    foreach ($methods as $method_name) {
      $reflectionMethod = new \ReflectionMethod($this->updateClassName, $method_name);
      $annotations = $this->annotationsReader->getMethodAnnotation($reflectionMethod, 'Acquia\Blt\Annotations\Update');
      if ($annotations) {
        $update_methods[$method_name] = $annotations;
      }
    }

    return $update_methods;
  }

  /**
   * @param string $command
   * @param string $cwd
   * @param bool $display_output
   *   Optional. Whether to print command output to screen. Changes return
   *   value.
   * @param bool $mustRun
   *
   * @return bool|string
   *   If $display_output is true, method will return TRUE for success, FALSE
   *   for failure. If $display_output false, method will return command output.
   */
  public static function executeCommand($command, $cwd = null, $display_output = true, $mustRun = true)
  {
    $timeout = 10800;
    $env = [
        'COMPOSER_PROCESS_TIMEOUT' => $timeout
      ] + $_ENV;
    $process = new Process($command, $cwd, $env, null, $timeout);
    $method = $mustRun ? 'mustRun' : 'run';
    if ($display_output) {
      $process->$method(function ($type, $buffer) {
        print $buffer;
      });
      return $process->isSuccessful();
    } else {
      $process->$method();
      return $process->getOutput();
    }
  }

  /**
   * Removes a patch from repo's root composer.json file.
   *
   * @param string $package
   *   The composer package name. E.g., 'drupal/features'.
   *
   * @param string $url
   *   The URL of the patch.
   *
   * @return bool
   *   TRUE if patch was removed, otherwise FALSE.
   */
  public function removePatch($package, $url) {
    $composer_json_filepath = $this->repoRoot . '/composer.json';
    $composer_json = json_decode(file_get_contents($composer_json_filepath), TRUE);
    if (!empty($composer_json['extra']['patches'][$package])) {
      foreach ($composer_json['extra']['patches'][$package] as $key => $patch_url) {
        if ($patch_url == $url) {
          unset($composer_json['extra']['patches'][$package][$key]);
          // If that was the only patch for this module, unset the parent too.
          if (empty($composer_json['extra']['patches'][$package])) {
            unset($composer_json['extra']['patches'][$package]);
          }
          file_put_contents($composer_json_filepath, json_encode($composer_json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

          return TRUE;
        }
      }
    }
    return FALSE;
  }
}
