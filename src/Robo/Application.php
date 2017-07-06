<?php

namespace Acquia\Blt\Robo;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
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
   * This command is identical to its parent, but public rather than protected.
   */
  public function runCommand(Command $command, InputInterface $input, OutputInterface $output) {
    return $this->doRunCommand($command, $input, $output);
  }

  /**
   * @{inheritdoc}
   */
  protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output) {
    $exit_code = parent::doRunCommand($command, $input, $output);

    // If we disabled a command, do not consider it a failure. Because of this
    // logic, we MUST throw exceptions in all cases of failure for BLT commands.
    // Otherwise, we might disable a command, execute it in the VM via
    // executeInDrupalVm(), and return a false success.
    if ($exit_code == ConsoleCommandEvent::RETURN_CODE_DISABLED) {
      $exit_code = 0;
    }

    return $exit_code;
  }

}
