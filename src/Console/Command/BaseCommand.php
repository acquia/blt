<?php

namespace Acquia\Blt\Console\Command;

use Symfony\Component\Console\Command\Command;

/**
 *
 */
class BaseCommand extends Command {

  /**
   * Array_merge_recursive does indeed merge arrays, but it converts values with duplicate
   * keys to arrays rather than overwriting the value in the first array with the duplicate
   * value in the second array, as array_merge does. I.e., with array_merge_recursive,
   * this happens (documented behavior):.
   *
   * Array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
   *     => array('key' => array('org value', 'new value'));
   *
   * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
   * Matching keys' values in the second array overwrite those in the first array, as is the
   * case with array_merge, i.e.:
   *
   * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
   *     => array('key' => array('new value'));
   *
   * Parameters are passed by reference, though only for performance reasons. They're not
   * altered by this function.
   *
   * Additionally, array_merge_recursive_distinct will not overwrite numerically keyed rows.
   * Instead it will append them to the parent array.
   *
   * @param array $array1
   * @param array $array2
   *
   * @return array
   *
   * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
   * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
   * @author Matthew Grasmick <matt (dot) grasmick (at) gmail (dot) com>
   * @see http://php.net/manual/en/function.array-merge-recursive.php#92195
   */
  protected function arrayMergeRecursiveDistinct(array &$array1, array &$array2) {
    $merged = $array1;

    foreach ($array2 as $key => &$value) {
      if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
        $merged[$key] = $this->arrayMergeRecursiveDistinct($merged[$key], $value);
      }
      else {
        $merged[$key] = $value;
      }
    }

    return $merged;
  }

}
