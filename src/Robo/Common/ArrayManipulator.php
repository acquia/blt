<?php

namespace Acquia\Blt\Robo\Common;

use Dflydev\DotAccessData\Data;

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
      if (is_array($value) && isset ($merged [$key]) && is_array($merged [$key])) {
        $merged [$key] = self::arrayMergeRecursiveDistinct($merged [$key],
          $value);
      }
      else {
        $merged [$key] = $value;
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
    $data = new Data($array);

    return $data->export();
  }
}
