<?php

namespace Acquia\Blt\Robo\Log;

use Robo\Log\RoboLogStyle;

class BltLogStyle extends RoboLogStyle {
  /**
   * Log style customization for Robo: add the time indicator to the
   * end of the log message if it exists in the context.
   *
   * @param string $label
   * @param string $message
   * @param array $context
   * @param string $taskNameStyle
   * @param string $messageStyle
   *
   * @return string
   */
  protected function formatMessage($label, $message, $context, $taskNameStyle, $messageStyle = '')
  {
    $message = parent::formatMessage($label, $message, $context, $taskNameStyle, $messageStyle);
    $message = trim($message);

    return $message;
  }
}
