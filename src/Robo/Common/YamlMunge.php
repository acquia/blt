<?php

namespace Acquia\Blt\Robo\Common;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Munges two yaml files.
 */
class YamlMunge {

  /**
   * Merges the arrays in two yaml files.
   *
   * @param string $file1
   *   The file path of the first file.
   * @param string $file2
   *   The file path of the second file.
   *
   * @return array
   *   The merged arrays.
   */
  public static function mungeFiles($file1, $file2) {
    $file1_contents = (array) self::parseFile($file1);
    $file2_contents = (array) self::parseFile($file2);

    return self::arrayMergeRecursiveExceptEmpty($file1_contents, $file2_contents);
  }

  /**
   * @param $array
   * @param $file
   * @param bool $overwrite
   */
  public static function mergeArrayIntoFile($array, $file, $overwrite = TRUE) {
    if (file_exists($file)) {
      $file_contents = (array) self::parseFile($file);
      if ($overwrite) {
        $new_contents = ArrayManipulator::arrayMergeRecursiveDistinct($file_contents, $array);
      }
      else {
        $new_contents = ArrayManipulator::arrayMergeRecursiveDistinct($array, $file_contents);
      }
    }
    else {
      $new_contents = $array;
    }

    self::writeFile($file, $new_contents);
  }

  /**
   * Parses a yaml file.
   *
   * @param string $file
   *   The file path.
   *
   * @return array
   *   The parsed yaml file.
   *
   * @throws \Symfony\Component\Yaml\Exception\ParseException
   */
  public static function parseFile($file) {
    return Yaml::parse(file_get_contents($file));
  }

  /**
   * @params string $file
   * @param array $contents
   */
  public static function writeFile($file, $contents) {
    $fs = new Filesystem();
    $yaml_string = Yaml::dump($contents, 3, 2);
    $fs->dumpFile($file, $yaml_string);
  }

  /**
   * Recursively merges arrays UNLESS second array is empty.
   *
   * Preserves data types. If value in second array is empty, it will REPLACE
   * the corresponding key in the first array, rather than being merged.
   *
   * @param array $array1
   *   The first array.
   * @param array $array2
   *   The second array.
   *
   * @return array
   */
  public static function arrayMergeRecursiveExceptEmpty(array &$array1, array &$array2) {
    $merged = $array1;

    foreach ($array2 as $key => &$value) {
      if (is_array($value) && isset($merged[$key]) && is_array($merged[$key]) && !empty($value)) {
        $merged[$key] = self::arrayMergeRecursiveExceptEmpty($merged[$key], $value);
      }
      else {
        $merged[$key] = $value;
      }
    }

    return $merged;
  }

}
