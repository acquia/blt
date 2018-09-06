<?php

namespace Acquia\Blt\Robo\Log;

use Robo\Log\RoboLogStyle;

/**
 * Defines style for BLT logging.
 */
class BltLogStyle extends RoboLogStyle {

  /**
   * Log style customization for Robo.
   *
   * @param string $label
   *   The log event label.
   * @param string $message
   *   The log message.
   * @param array $context
   *   The context, e.g., $context['time'] for task duration.
   * @param string $taskNameStyle
   *   The style wrapper for the label, e.g., 'comment' for
   *   '<comment></comment>'.
   * @param string $messageStyle
   *   The style wrapper for the message, e.g., 'comment' for
   *   '<comment></comment>'.
   *
   * @return string
   *   The formatted message.
   */
  protected function formatMessage($label, $message, $context, $taskNameStyle, $messageStyle = '') {
    $message = parent::formatMessage($label, $message, $context, $taskNameStyle, $messageStyle);
    $message = trim($message);

    return $message;
  }

}
