<?php

namespace Acquia\Blt\Robo\Common;

use Symfony\Component\Console\Question\ConfirmationQuestion;

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
   * @param string $message
   *
   * @return string
   */
  protected function formatQuestion($message) {
    return "<question> $message</question> ";
  }

  /**
   * @param string $question
   * @param bool $default
   *
   * @return string
   */
  protected function confirm($question, $default = FALSE)
  {
    if ($this->input()->hasOption('yes') && $this->input()->getOption('yes')) {
      return TRUE;
    }

    return $this->doAsk(new ConfirmationQuestion($this->formatQuestion($question . ' (y/n)'), $default));
  }

}
