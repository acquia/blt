<?php

namespace Acquia\Blt\Robo\Common;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * An extension of \Robo\Common\IO.
 */
trait IO {

  use \Robo\Common\IO;

  /**
   * Writes text to screen, without decoration.
   *
   * @param string $text
   *   The text to write.
   */
  protected function say($text) {
    $this->writeln($text);
  }

  /**
   * Writes text to screen with big, loud decoration.
   *
   * @param string $text
   *   The text to write.
   * @param int $length
   *   The length at which text should be wrapped.
   * @param string $color
   *   The color of the text.
   */
  protected function yell($text, $length = 40, $color = 'green') {
    $format = "<fg=white;bg=$color;options=bold>%s</fg=white;bg=$color;options=bold>";
    $this->formattedOutput($text, $length, $format);
  }

  /**
   * Format text as a question.
   *
   * @param string $message
   *   The question text.
   *
   * @return string
   *   The formatted question text.
   */
  protected function formatQuestion($message) {
    return "<question> $message</question> ";
  }

  /**
   * Prompts a user to confirm an action.
   *
   * This integrates the global --yes option and permits a default to be
   * defined.
   *
   * @param string $question
   *   The question text.
   * @param bool $default
   *   The default value, if user indicated --no-interaction.
   *
   * @return string
   *   The response.
   */
  protected function confirm($question, $default = FALSE) {
    if ($this->input()->hasOption('yes') && $this->input()->getOption('yes') !== FALSE) {
      return TRUE;
    }

    return $this->doAsk(new ConfirmationQuestion($this->formatQuestion($question . ' (y/n)'), $default));
  }

  /**
   * Asks the user a multiple-choice question.
   *
   * @param string $question
   *   The question text.
   * @param array $options
   *   An array of available options.
   *
   * @return string
   *   The chosen option.
   */
  protected function askChoice($question, $options, $default = NULL) {
    return $this->doAsk(new ChoiceQuestion($this->formatQuestion($question),
      $options, $default));
  }

  /**
   * Asks a required question.
   *
   * @param string $message
   *   The question text.
   *
   * @return string
   *   The response.
   */
  protected function askRequired($message) {
    $question = new Question($this->formatQuestion($message));
    $question->setValidator(function ($answer) {
      if (empty($answer)) {
        throw new \RuntimeException(
          'You must enter a value!'
        );
      }

      return $answer;
    });
    return $this->doAsk($question);
  }

  /**
   * Writes an array to the screen as a formatted table.
   *
   * @param array $array
   *   The unformatted array.
   * @param array $headers
   *   The headers for the array. Defaults to ['Property','Value'].
   */
  protected function printArrayAsTable(
    array $array,
    array $headers = ['Property', 'Value']
  ) {
    $table = new Table($this->output);
    $table->setHeaders($headers)
      ->setRows(ArrayManipulator::convertArrayToFlatTextArray($array))
      ->render();
  }

  /**
   * Writes a particular configuration key's value to the log.
   *
   * @param array $array
   *   The configuration.
   * @param string $prefix
   *   A prefix to add to each row in the configuration.
   * @param int $verbosity
   *   The verbosity level at which to display the logged message.
   */
  protected function logConfig(array $array, $prefix = '', $verbosity = OutputInterface::VERBOSITY_VERY_VERBOSE) {
    if ($this->output()->getVerbosity() >= $verbosity) {
      if ($prefix) {
        $this->output()->writeln("<comment>Configuration for $prefix:</comment>");
        foreach ($array as $key => $value) {
          $array["$prefix.$key"] = $value;
          unset($array[$key]);
        }
      }
      $this->printArrayAsTable($array);
    }
  }

}
