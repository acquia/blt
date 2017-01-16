<?php

namespace Acquia\Blt\Robo\Common;

trait LocalEnvironmentValidator {

  use StringManipulator;

  /**
   * Check if an array of commands exists on the system.
   *
   * @param $commands array An array of command binaries.
   *
   * @return bool
   *   TRUE if all commands exist, otherwise FALSE.
   */
  protected function checkCommandsExist($commands) {
    foreach ($commands as $command) {
      if (!$this->commandExists($command)) {
        $this->yell("Unable to find '$command' command!");
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Checks if a given command exists on the system.
   *
   * @param $command string the command binary only. E.g., "drush" or "php".
   *
   * @return bool
   *   TRUE if the command exists, otherwise FALSE.
   */
  protected function commandExists($command) {
    exec("command -v $command >/dev/null 2>&1", $output, $exit_code);
    return $exit_code == 0;
  }
}
