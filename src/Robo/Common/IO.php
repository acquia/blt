<?php

namespace Acquia\Blt\Robo\Common;

/**
 *
 */
trait IO {

  use \Robo\Common\IO;

  /**
   * @param string $text
   */
  protected function say($text) {
    $this->writeln($text);
  }

  /**
   *
   *
   * @param string $text
   * @param int $length
   * @param string $color
   */
  protected function yell($text, $length = 40, $color = 'green') {
    $format = "<fg=white;bg=$color;options=bold>%s</fg=white;bg=$color;options=bold>";
    $this->formattedOutput($text, $length, $format);
  }

  /**
   * @param $text
   * @param string $color
   */
  protected function warn($text, $color = 'yellow') {
    $this->yell($text, NULL, $color);
  }

  /**
   * @param $text
   * @param string $color
   */
  protected function error($text, $color = 'red') {
    $this->yell($text, NULL, $color);
  }

  /**
   * @param string $message
   *
   * @return string
   */
  protected function formatQuestion($message) {
    return "<question> $message</question> ";
  }

}
