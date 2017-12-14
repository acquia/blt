<?php

namespace Acquia\Blt\Robo\Filesets;

use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Acquia\Blt\Robo\Exceptions\BltException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\IndexedReader;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Robo\Contract\ConfigAwareInterface;

/**
 * Manages BLT filesets.
 *
 * Will load BLT core filesets and custom filesets.
 *
 * @package Acquia\Blt\Robo\Common
 */
class FilesetManager implements ConfigAwareInterface, LoggerAwareInterface {

  use ConfigAwareTrait;
  use LoggerAwareTrait;

  /**
   * @var \Symfony\Component\Finder\Finder[]
   */
  protected $filesets = [];

  /**
   * @var \Doctrine\Common\Annotations\IndexedReader
   */
  protected $annotationsReader;

  /**
   * FilesetManager constructor.
   */
  public function __construct() {
    AnnotationRegistry::registerFile(__DIR__ . '/../../Annotations/Fileset.php');
    $this->annotationsReader = new IndexedReader(new AnnotationReader());
  }

  /**
   * Registers filesets.
   *
   * Finds and instantiates all filesets by scanning $classes for @fileset
   * annotations.
   */
  public function registerFilesets() {
    // @todo Assert that filesets from \Acquia\Blt\Custom\Filesets override
    // those from \Acquia\Blt\Custom\Filesets.
    $classes = [
      // @codingStandardsIgnoreStart
      \Acquia\Blt\Robo\Filesets\Filesets::class,
      \Acquia\Blt\Custom\Filesets::class,
      // @codingStandardsIgnoreEnd
    ];
    $fileset_annotations = $this->getAllFilesetAnnotations($classes);
    $filesets = $this->getFilesetsFromAnnotations($fileset_annotations);

    $this->filesets = $filesets;
  }

  /**
   * Gets all @fileset annotated methods from $classes.
   *
   * @param array $classes
   *   A flat array of classes to be scanned for annotated methods.
   *
   * @return array
   *   An array of annotated methods, keyed by fileset id.
   *   Example value:
   *     [\Acquia\Blt\Robo\Filesets\Filesets::class => [
   *       'files.php.tests' => ['getFilesetPhpTests'],
   *     ]].
   */
  protected function getAllFilesetAnnotations($classes) {
    $fileset_annotations = [];
    foreach ($classes as $class) {
      $fileset_annotations[$class] = isset($fileset_annotations[$class]) ? $fileset_annotations[$class] : [];
      if (class_exists($class)) {
        $fileset_annotations[$class] = array_merge($fileset_annotations[$class],
          $this->getClassFilesetAnnotations($class));
      }
    }
    return $fileset_annotations;
  }

  /**
   * Gets all @fileset annotated methods for a given $class..
   *
   * @param string $class
   *   The class to be scanned for annotated methods.
   *
   * @return array
   *   An array of annotated methods, keyed by fileset id.
   *   E.g, ['files.php.tests' => 'getFilesetPhpTests'].
   */
  protected function getClassFilesetAnnotations($class) {
    $filesets = [];
    $methods = get_class_methods($class);
    foreach ($methods as $method_name) {
      $reflectionMethod = new \ReflectionMethod($class, $method_name);
      $annotations = $this->annotationsReader->getMethodAnnotation($reflectionMethod, 'Acquia\Blt\Annotations\Fileset');
      if ($annotations) {
        $filesets[$annotations->id] = $method_name;
      }
    }

    return $filesets;
  }

  /**
   * Gets $this->filesets. Registers filesets if they are unset.
   *
   * @return \Symfony\Component\Finder\Finder[]
   *   An array of instantiated filesets.
   *
   * @throws \Acquia\Blt\Robo\Exceptions\BltException
   */
  public function getFilesets($fileset_ids = []) {
    if (!$this->filesets) {
      $this->registerFilesets();
    }

    if ($fileset_ids) {
      foreach ($fileset_ids as $fileset_id) {
        if (!in_array($fileset_id, array_keys($this->filesets))) {
          throw new BltException("Unable to find fileset $fileset_id!");
        }
        $filesets[$fileset_id] = $this->filesets[$fileset_id];
      }
      return $filesets;
    }

    return $this->filesets;
  }

  /**
   * Gets a specific fileset from $this->filesets.
   *
   * @param string $id
   *   The fileset id.
   *
   * @return \Symfony\Component\Finder\Finder|null
   *   The fileset.
   */
  public function getFileset($id) {
    $filesets = $this->getFilesets();
    if (isset($filesets[$id])) {
      return $filesets[$id];
    }
    $this->logger->warning("Fileset $id not found");

    return NULL;
  }

  /**
   * Gets an array of instantiated filesets, given an array of annotations.
   *
   * @param array $fileset_annotations
   *   An array of fileset annotations, as returned by
   *   $this->getAllFilesetAnnotations().
   *
   * @return \Symfony\Component\Finder\Finder[]
   *   An array of instantiated filesets.
   */
  protected function getFilesetsFromAnnotations($fileset_annotations) {
    $filesets = [];
    $this->logger->debug("Gathering filesets from annotated methods...");;
    foreach ($fileset_annotations as $class => $fileset) {
      if (class_exists($class)) {
        $fileset_class = new $class();
        $fileset_class->setConfig($this->config);
        foreach ($fileset as $id => $method_name) {
          $this->logger->debug("Calling $method_name on $class object...");
          if (method_exists($fileset_class, $method_name)) {
            $filesets[$id] = call_user_func_array([$fileset_class, $method_name],
              []);
          }
        }
      }
    }
    return $filesets;
  }

  /**
   * Returns the intersection of $files and a given fileset.
   *
   * @param array $files
   *   An array of absolute file paths.
   * @param \Symfony\Component\Finder\Finder $fileset
   *   The ID for a given fileset.
   *
   * @return \Symfony\Component\Finder\Finder
   *   The intersection of $files and the fileset.
   */
  public function filterFilesByFileset($files, $fileset) {
    $absolute_files = array_map(array($this, 'prependRepoRoot'), $files);

    // @todo Compare performance of this vs. using
    // array_intersect($files, array_keys(iterator_to_array($fileset)));
    $filter = function (\SplFileInfo $file) use ($absolute_files) {
      if (!in_array($file->getRealPath(), $absolute_files)) {
        return FALSE;
      }
    };
    $fileset->filter($filter);

    return $fileset;
  }

  /**
   * Prepends the repo.root variable to a given filepath.
   *
   * @param string $relative_path
   *   A file path relative to repo.root.
   *
   * @return string
   *   The absolute file path.
   */
  protected function prependRepoRoot($relative_path) {
    $absolute_path = $this->getConfigValue('repo.root') . '/' . $relative_path;

    return $absolute_path;
  }

}
