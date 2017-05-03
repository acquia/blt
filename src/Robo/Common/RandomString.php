<?php

namespace Acquia\Blt\Robo\Common;

/**
 * Utility class for generating random strings.
 */
class RandomString {

  /**
   * The maximum number of times name() and string() can loop.
   *
   * This prevents infinite loops if the length of the random value is very
   * small.
   *
   * @see \Drupal\Tests\Component\Utility\RandomTest
   */
  const MAXIMUM_TRIES = 100;

  /**
   * Generates a random string of ASCII characters of codes 32 to 126.
   *
   * The generated string includes alpha-numeric characters and common
   * miscellaneous characters. Use this method when testing general input
   * where the content is not restricted.
   *
   * @param int $length
   *   Length of random string to generate.
   * @param bool $unique
   *   (optional) If TRUE ensures that the random string returned is unique.
   *   Defaults to FALSE.
   * @param callable $validator
   *   (optional) A callable to validate the string. Defaults to NULL.
   * @param string $characters
   *   (optional) A string containing all possible characters that may be used
   *   to generate the random string.
   *
   * @return string
   *   Randomly generated string.
   *
   * @see \Drupal\Component\Utility\Random::name()
   */
  public static function string($length = 8, $unique = FALSE, callable $validator = NULL, $characters = '') {
    $counter = 0;
    $strings = [];
    $characters_array = $characters ? str_split($characters) : [];

    // Continue to loop if $unique is TRUE and the generated string is not
    // unique or if $validator is a callable that returns FALSE. To generate a
    // random string this loop must be carried out at least once.
    do {
      if ($counter == static::MAXIMUM_TRIES) {
        throw new \RuntimeException('Unable to generate a unique random name');
      }
      $str = '';
      for ($i = 0; $i < $length; $i++) {
        if ($characters_array) {
          $position = mt_rand(0, count($characters_array) - 1);
          $str .= $characters_array[$position];
        }
        else {
          $str .= chr(mt_rand(32, 126));
        }
      }
      $counter++;

      $continue = FALSE;
      if ($unique) {
        $continue = isset($strings[$str]);
      }
      if (!$continue && is_callable($validator)) {
        // If the validator callback returns FALSE generate another random
        // string.
        $continue = !call_user_func($validator, $str);
      }
    } while ($continue);

    if ($unique) {
      $strings[$str] = TRUE;
    }

    return $str;
  }

}
