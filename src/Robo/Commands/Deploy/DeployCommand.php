<?php

namespace Acquia\Blt\Robo\Commands\Deploy;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Common\RandomString;

/**
 * Defines commands in the "deploy:*" namespace.
 */
class DeployCommand extends BltTasks {

  /**
   * Builds separate artifact and pushes to git.remotes defined project.yml
   *
   * @command deploy
   */
  public function deploy() {
    $this->checkDirty();

    $create_tag = FALSE;
    if (empty($this->getConfigValue('deploy.tag'))) {
      $this->say("Typically, you would only create a tag if you currently have a tag checked out on your source repository.");
      $create_tag = $this->confirm("Would you like to create a tag?", $create_tag);
    }

    if ($create_tag) {
      $this->createTag();
    }
    else {
      $this->createBranch();
    }
  }

  protected function checkDirty() {
    $dirty = (bool) $this->taskExec('git status --porcelain')
      ->printMetadata(FALSE)
      ->printOutput(TRUE)
      ->run();
    if ($dirty) {
      if ($this->getConfigValue('deploy.git.failOnDirty')) {
        throw new \Exception("There are uncommitted changes, commit or stash these changes before deploying.");
      }
      else {
        $this->logger->warning("Deploy is being run with uncommitted changes.");
      }
    }
  }

  protected function getCommitMessage() {
    if (empty($this->getConfigValue('deploy.commitMsg'))) {
      $git_last_commit_message = explode(' ', shell_exec("git log --oneline -1"), 2);
      return $this->askDefault('Enter a valid commit message', $git_last_commit_message);
    }

    return $this->getConfigValue('deploy.commitMsg');
  }

  protected function getBranchName() {
    $git_current_branch = shell_exec("git rev-parse --abbrev-ref HEAD");
    $default_branch = $git_current_branch . '-build';
    $branch_name = $this->askDefault('Enter the branch name for the deployment artifact', $default_branch);

    return $branch_name;
  }

  protected function createTag() {
    $tag_name = $this->ask('Enter the tag name for the deployment artifact');
  }

  protected function createBranch() {
    $branch_name = $this->getBranchName();
  }

  protected function prepareDir() {

  }

  protected function addRemotes() {

  }

  protected function build() {

  }

  protected function commit() {

  }
}
