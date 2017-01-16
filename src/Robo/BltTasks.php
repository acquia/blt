<?php

namespace Acquia\Blt\Robo;

use Robo\Common\IO;
use Robo\Tasks;

class BltTasks extends Tasks
{
  /**
   * @param string $text
   */
  protected function say($text)
  {
    $this->writeln($text);
  }

  /**
   *
   *
   * @param string $text
   * @param int $length
   * @param string $color
   */
  protected function yell($text, $length = 40, $color = 'green')
  {
    $format = "<fg=white;bg=$color;options=bold>%s</fg=white;bg=$color;options=bold>";
    $this->formattedOutput($text, $length, $format);
  }

  protected function warn($text, $color = 'yellow') {
    $this->yell($text, null,  $color);
  }

  /**
   * @param string $message
   *
   * @return string
   */
  protected function formatQuestion($message)
  {
    return  "<question> $message</question> ";
  }
}
