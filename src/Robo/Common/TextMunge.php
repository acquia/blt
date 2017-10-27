<?php

namespace Acquia\Blt\Robo\Common;

use function array_values;

/**
 * Munges two text files.
 */
class TextMunge {

  /**
   * Merges the arrays in two text files.
   *
   * @param string $file1
   *   The file path of the first file.
   * @param string $file2
   *   The file path of the second file.
   *
   * @return string
   *   The merged arrays, in yaml format.
   */
  public static function munge($file1, $file2) {
    $file1_contents = file($file1);
    $file2_contents = file($file2);

    $munged_contents = self::arrayMergeNoDuplicates($file1_contents, $file2_contents);

    return (string) $munged_contents;
  }

  /**
   * Merges two arrays and removes duplicate values.
   *
   * @param array $array1
   *   The first array.
   * @param array $array2
   *   The second array.
   *
   * @return array
   */
  public static function arrayMergeNoDuplicates(array &$array1, array &$array2) {
    $merged = array_merge($array1, $array2);
    $merged_without_dups = array_unique($merged);
    $merged_rekeyed = array_values($merged_without_dups);

    return $merged_rekeyed;
  }

}
