<?php

namespace Acquia\Blt\Robo;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application as ConsoleApplication;

/**
 * Class Application.
 *
 * @package Acquia\Blt\Robo
 */
class Application extends ConsoleApplication {

  /**
   * @param \Symfony\Component\Console\Command\Command $command
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *
   * @return int
   *   The exit code.
   */
  public function runCommand(Command $command, InputInterface $input, OutputInterface $output) {
    return $this->doRunCommand($command, $input, $output);
  }

}
