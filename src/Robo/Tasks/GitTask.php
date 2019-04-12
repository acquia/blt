<?php

namespace Acquia\Blt\Robo\Tasks;

use Robo\Task\Vcs\GitStack;

class GitTask extends GitStack {

  public function commit($message, $options = "")
  {
    $git_name = $this->getConfigValue('git.user.name');
    $git_email = $this->getConfigValue('git.user.email');
    if ($git_name && $git_email) {
      $options .= ' --author ';
      $options .= escapeshellarg(sprintf('"%s <%s>"', $git_name, $git_email));
    }
    return parent::commit($message, $options);
  }
}
