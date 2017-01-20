<?php

namespace Acquia\Blt\Robo\Common;

use Dflydev\DotAccessData\Data;

/**
 *
 */
class ArrayManipulator {

  /**
   * @param array $array1
   * @param array $array2
   *
   * @return array
   */
  public static function arrayMergeRecursiveDistinct(
    array &$array1,
    array &$array2
  ) {
    $merged = $array1;
    foreach ($array2 as $key => &$value) {
      if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
        $merged[$key] = self::arrayMergeRecursiveDistinct($merged[$key],
          $value);
      }
      else {
        $merged[$key] = $value;
      }
    }
    return $merged;
  }

  /**
   * @param $array
   *
   * @return array
   */
  public static function reKeyDotNotatedKeys($array) {
    $data = new Data();

    // @todo Make this work at all levels of array.
    foreach ($array as $key => $value) {
      $data->set($key, $value);
    }

    return $data->export();
  }

  /**
   * @param $array
   *
   * @return array
   */
  public static function convertArrayFlatTextArray($array) {
    $rows = [];
    $max_line_length = 80;
    foreach ($array as $key => $value) {
      if (is_array($value)) {

        if (is_numeric(key($value))) {
          $row_contents = implode("\n", $value);
          $rows[] = [
            $key,
            wordwrap($row_contents, $max_line_length, "\n", TRUE)
          ];
        }
        else {
          $rows[] = [$key, ''];
          foreach ($value as $sub_key => $sub_value) {
            $rows[] = [
              ' - ' . $sub_key,
              wordwrap($sub_value, $max_line_length, "\n", TRUE)
            ];
          }
        }

        if (count($value) > 1) {
          // $rows[] = new TableSeparator();
        }
      }
      else {
        if ($value === TRUE) {
          $contents = 'true';
        }
        elseif ($value === FALSE) {
          $contents = 'false';
        }
        else {
          $contents = wordwrap($value, $max_line_length, "\n", TRUE);
        }
        $rows[] = [$key, $contents];
      }
    }

    return $rows;
  }

}
