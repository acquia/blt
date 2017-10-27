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
   *
   * @return int
   */
  public function commitMsgHook($message) {
    $this->say('Validating commit message syntax...');
    $pattern = $this->getConfigValue('git.commit-msg.pattern');
    if (!preg_match($pattern, $message)) {
      $this->logger->error("Invalid commit message!");
      $this->say("Commit messages must conform to the regex $pattern");
      $this->logger->notice("To disable this command, see http://blt.rtfd.io/en/8.x/readme/extending-blt/#disabling-a-command");
      $this->logger->notice("To customize git hooks, see http://blt.rtfd.io/en/8.x/readme/extending-blt/#setupgit-hooks.");

      return 1;
    }

    return 0;
  }

  /**
   * Validates staged files.
   *
   * @command git:pre-commit
   *
   * @param string $changed_files
   *   A list of staged files, separated by \n.
   *
   * @return \Robo\Result
   */
  public function preCommitHook($changed_files) {
    $collection = $this->collectionBuilder();
    $collection->setProgressIndicator(NULL);
    $collection->addCode(
      function () use ($changed_files) {
        return $this->invokeCommands([
          'validate:phpcs:files' => ['file_list' => $changed_files],
          'validate:twig:files' => ['file_list' => $changed_files],
          'validate:yaml:files' => ['file_list' => $changed_files],
        ]);
      }
    );

    $changed_files_list = explode("\n", $changed_files);
    if (in_array('composer.json', $changed_files_list)
      || in_array('composer.lock', $changed_files_list)) {
      $collection->addCode(
        function () use ($changed_files) {
          return $this->invokeCommand('validate:composer');
        }
      );
    }

    $result = $collection->run();

    if ($result->wasSuccessful()) {
      $this->say("<info>Your local code has passed git pre-commit validation.</info>");
    }

    return $result;
  }

}
