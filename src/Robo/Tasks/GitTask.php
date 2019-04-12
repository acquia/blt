<?php

namespace Acquia\Blt\Robo\Tasks;

use Robo\Task\Vcs\GitStack;

/**
 * Class GitTask
 * @package Acquia\Blt\Robo\Tasks
 *
 * Runs Git tasks using BLT-specific helpers, such as setting the commit author.
 */
class GitTask extends GitStack {

  /**
   * @inheritDoc
   */
  public function commit($message, $options = "") {
    $git_name = $this->getConfigValue('git.user.name');
    $git_email = $this->getConfigValue('git.user.email');
    if ($git_name && $git_email) {
      $options .= ' --author ';
      $options .= escapeshellarg(sprintf('"%s <%s>"', $git_name, $git_email));
    }
    return parent::commit($message, $options);
  }

}
