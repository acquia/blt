<?php

namespace Acquia\Blt\Tests;

use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class BufferedConsoleOutput
 *
 * This is a combination of BufferedOutput and ConsoleOutput. It allows us
 * to capture output in a buffer for PHPUnit assertions and simultaneously
 * print output to the console for debugging.
 *
 * @package Acquia\Blt\Tests
 * @see \Symfony\Component\Console\Output\BufferedOutput
 * @see \Symfony\Component\Console\Output\ConsoleOutput
 */
class BufferedConsoleOutput extends ConsoleOutput {

  /**
   * Empties buffer and returns its content.
   *
   * @return string
   */
  public function fetch() {
    // For some reason, some blt commands do not appear to always use the same
    // output buffer that is passed to it. recipes:multisite:init works, setup
    // does not. Moving buffer to global namespace works around this.
    $content = $GLOBALS['blt_phpunit_buffer_output'];
    $GLOBALS['blt_phpunit_buffer_output'] = '';

    return $content;
  }

  /**
   * {@inheritdoc}
   */
  protected function doWrite($message, $newline) {
    if (!array_key_exists('blt_phpunit_buffer_output', $GLOBALS)) {
      $GLOBALS['blt_phpunit_buffer_output'] = '';
    }

    $GLOBALS['blt_phpunit_buffer_output'] .= $message;

    if ($newline) {
      $GLOBALS['blt_phpunit_buffer_output'] .= PHP_EOL;
    }

    parent::doWrite($message, $newline);
  }

}
