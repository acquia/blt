<?php

namespace Acquia\Blt\Robo\Filesets;

use Acquia\Blt\Robo\Config\ConfigAwareTrait;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\IndexedReader;
use Robo\Contract\ConfigAwareInterface;
use Symfony\Component\Finder\Finder;

/**
 * Manages BLT filesets.
 *
 * Will load BLT core filesets and custom filesets.
 *
 * @package Acquia\Blt\Robo\Common
 */
class FilesetManager implements ConfigAwareInterface {

  use ConfigAwareTrait;

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
    $classes = [
      \Acquia\Blt\Robo\Filesets\Filesets::class,
      \Acquia\Blt\Custom\Filesets::class
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
      if (class_exists($class)) {
        $fileset_annotations[$class] = array_merge($fileset_annotations,
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
   */
  public function getFilesets() {
    if (!$this->filesets) {
      $this->registerFilesets();
    }

    return $this->filesets;
  }

  /**
   * Gets a specific fileset from $this->filesets.
   *
   * @param string $id
   *   The fileset id.
   *
   * @return \Symfony\Component\Finder\Finder|NULL
   *   The fileset.
   */
  public function getFileset($id) {
    $filesets = $this->getFilesets();
    if (isset($filesets[$id])) {
      return $filesets[$id];
    }

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
    foreach ($fileset_annotations as $class => $fileset) {
      $fileset_class = new $class();
      $fileset_class->setConfig($this->config);
      foreach ($fileset as $id => $method_name) {
        $filesets[$id] = $fileset_class->$method_name();
      }
    }
    return $filesets;
  }

}
