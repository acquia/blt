<?php

namespace Acquia\Blt\Robo\Commands\Git;

use Acquia\Blt\Robo\BltTasks;

/**
 * Defines commands in the "git:*" namespace.
 */
class GitCommand extends BltTasks {

  /**
   * Validates a git commit message.
   *
   * @command git:commit-msg
   */
  public function commitMsg($message) {
    $prefix = $this->getConfigValue('project.prefix');
    if (!preg_match("/^$prefix-[0-9]+(: )[^ ].{15,}\\./", $message)) {
      $this->logger->error("Invalid commit message!");
      $this->say("Commit messages must:");
      $this->say("* Contain the project prefix followed by a hyphen");
      $this->say("* Contain a ticket number followed by a colon and a space");
      $this->say("* Be at least 15 characters long and end with a period.");
      $this->say("Valid example: $prefix-135: Added the new picture field to the article feature.");

      return 1;
    }
  }

}
