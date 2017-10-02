<?php

namespace Acquia\Blt\Robo\Commands\Input;

use Symfony\Component\Console\Input\ArrayInput as ArrayInputBase;

/**
 * ArrayInput class.
 */
class ArrayInput extends ArrayInputBase {

  /**
   * Escapes a token through escapeshellarg if it contains unsafe chars.
   *
   * @param array|string $token
   *
   * @return string
   */
  public function escapeToken($token) {
    // Account for ArrayInput arguments possibly being arrays to prevent
    // warning when casting to string.
    // @todo Remove when Drupal allows upgrade to Symfony Console 3.3.9+.
    // @see https://github.com/symfony/symfony/issues/24087.
    if (is_array($token)) {
      foreach ($token as $key => $value) {
        $token[$key] = preg_match('{^[\w-]+$}', $value) ? $value : escapeshellarg($value);
      }
      return implode(' ', $token);
    }
    return preg_match('{^[\w-]+$}', $token) ? $token : escapeshellarg($token);
  }

}
