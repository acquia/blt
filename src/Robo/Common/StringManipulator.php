<?php

namespace Acquia\Blt\Robo\Common;

/**
 * RoboConfigAwareTrait.
 */
class StringManipulator {

  /**
   * Trims the last $num_lines lines from end of a text string.
   *
   * @param string $text
   *   A string of text.
   * @param int $num_lines
   *   The number of lines to trim from the end of the text.
   *
   * @return string
   *   The trimmed text.
   */
  public static function trimEndingLines($text, $num_lines) {
    return implode("\n",
      array_slice(explode("\n", $text), 0, count($text) - $num_lines));
  }

  /**
   * Trims the last $num_lines lines from beginning of a text string.
   *
   * @param string $text
   *   A string of text.
   * @param int $num_lines
   *   The number of lines to trim from beginning of text.
   *
   * @return string
   *   The trimmed text.
   */
  public static function trimStartingLines($text, $num_lines) {
    return implode("\n", array_slice(explode("\n", $text), $num_lines));
  }

  /**
   * @param $identifier
   * @param array $filter
   *
   * @return mixed
   */
  public static function convertStringToMachineSafe($identifier, array $filter = array(
    ' ' => '_',
    '-' => '_',
    '/' => '_',
    '[' => '_',
    ']' => '',
  )) {
    $identifier = str_replace(array_keys($filter), array_values($filter), $identifier);
    // Valid characters are:
    // - a-z (U+0030 - U+0039)
    // - A-Z (U+0041 - U+005A)
    // - the underscore (U+005F)
    // - 0-9 (U+0061 - U+007A)
    // - ISO 10646 characters U+00A1 and higher
    // We strip out any character not in the above list.
    $identifier = preg_replace(
      '/[^\x{0030}-\x{0039}\x{0041}-\x{005A}\x{005F}\x{0061}-\x{007A}\x{00A1}-\x{FFFF}]/u',
      '',
      $identifier);
    // Identifiers cannot start with a digit, two hyphens, or a hyphen followed
    // by a digit.
    $identifier = preg_replace(array(
      '/^[0-9]/',
      '/^(-[0-9])|^(--)/',
    ), array('_', '__'), $identifier);
    return strtolower($identifier);
  }

  /**
   * @param $string
   *
   * @return mixed
   */
  public static function convertStringToPrefix($string) {
    $words = explode(' ', $string);
    $prefix = '';
    foreach ($words as $word) {
      $prefix .= substr($word, 0, 1);
    }
    return strtoupper($prefix);
  }

}
