<?php

namespace Acquia\Blt\Robo\Common;

/**
 * Munges two composer.json files.
 */
class ComposerMunge {

  /**
   * Selectively merges parts of two composer.json files.
   *
   * @param string $file1
   *   The file path to the first composer.json file.
   * @param string $file2
   *   The file path to the second composer.json file.
   *
   * @return string
   *   The new, merged composer.json contents.
   */
  public static function mungeFiles($file1, $file2) {
    $default_contents = [];
    $file1_contents = (array) json_decode(file_get_contents($file1), TRUE) + $default_contents;
    $file2_contents = (array) json_decode(file_get_contents($file2), TRUE) + $default_contents;

    $output = self::mergeKeyed($file1_contents, $file2_contents);

    // Ensure that require and require-dev are objects and not arrays.
    if (array_key_exists('require', $output) && is_array($output['require'])) {
      $output['require'] = (object) $output['require'];
    }
    if (array_key_exists('require-dev', $output)&& is_array($output['require-dev'])) {
      $output['require-dev'] = (object) $output['require-dev'];
    }

    $output_json = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    return $output_json;
  }

  /**
   * Merges specific keyed arrays and objects in composer.json files.
   *
   * @param $file1_contents
   * @param $file2_contents
   * @param array $exclude_keys
   *
   * @return mixed
   */
  protected static function mergeKeyed($file1_contents, $file2_contents, $exclude_keys = []) {
    // Merge keyed arrays objects.
    $merge_keys = [
      'config',
      'extra',
    ];
    $output = $file1_contents;
    foreach ($merge_keys as $key) {
      // Set empty keys to empty placeholder arrays.
      if (!array_key_exists($key, $file1_contents)) {
        $file1_contents[$key] = [];
      }
      if (!array_key_exists($key, $file2_contents)) {
        $file2_contents[$key] = [];
      }

      // Merge!
      $output[$key] = ArrayManipulator::arrayMergeRecursiveDistinct($file1_contents[$key], $file2_contents[$key]);
    }

    return $output;
  }

}
