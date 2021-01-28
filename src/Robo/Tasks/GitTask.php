<?php

namespace Acquia\Blt\Robo\Tasks;

use Robo\Task\Vcs\GitStack;

/**
 * Runs Git tasks using BLT-specific helpers, such as setting the commit author.
 *
 * @package Acquia\Blt\Robo\Tasks
 */
class GitTask extends GitStack {

  /**
   * Commit.
   *
   * @inheritDoc
   */
  public function commit($message, $options = "") {
    $message = escapeshellarg($message);
    $git_name = $this->getConfig()->get('git.user.name');
    $git_email = $this->getConfig()->get('git.user.email');

    $command = ['git'];
    if ($git_name && $git_email) {
      $command[] = '-c user.name=' . escapeshellarg($git_name);
      $command[] = '-c user.email=' . escapeshellarg($git_email);
    }
    $command[] = 'commit';
    $command[] = "-m $message";
    $command[] = $options;
    return $this->exec($command);
  }

  /**
   * Tag.
   *
   * @inheritDoc
   */
  public function tag($tag_name, $message = "") {
    $message = escapeshellarg($message);
    $tag_name = escapeshellarg($tag_name);
    $git_name = $this->getConfig()->get('git.user.name');
    $git_email = $this->getConfig()->get('git.user.email');

    $command = ['git'];
    if ($git_name && $git_email) {
      $command[] = '-c user.name=' . escapeshellarg($git_name);
      $command[] = '-c user.email=' . escapeshellarg($git_email);
    }
    $command[] = 'tag';
    $command[] = "-a $tag_name";
    $command[] = "-m $message";
    return $this->exec($command);
  }

}
