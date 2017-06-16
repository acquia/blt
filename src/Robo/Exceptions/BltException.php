<?php

namespace Acquia\Blt\Robo\Exceptions;

/**
 * Class BltException.
 *
 * @package Acquia\Blt\Robo\Exceptions
 */
class BltException extends \Exception {

  /**
   *
   */
  public function __construct(
    $message = "",
    $code = 0,
    \Throwable $previous = NULL
  ) {

    // @todo Check verbosity level. If not verbose, append "Re-run the command
    // with -v to see more verbose output."
    parent::__construct($message, $code, $previous);

    $this->transmitAnalytics();
  }

  /**
   * Transmit anonymous data about Exception.
   */
  protected function transmitAnalytics() {
    // Create new BltAnalyticsData class.
  }

}
