<?php

namespace Acquia\Blt\Robo\Commands\Git;

use Acquia\Blt\Robo\BltTasks;
use Robo\Contract\VerbosityThresholdInterface;

/**
 * Defines commands in the "git:*" namespace.
 */
class GitCommand extends BltTasks {

  /**
   * Validates a git commit message.
   *
   * @command internal:git-hook:execute:commit-msg
   * @aliases git:commit-msg
   * @hidden
   *
   * @return int
   */
  public function commitMsgHook($message) {
    $this->say('Validating commit message syntax...');
    $pattern = $this->getConfigValue('git.commit-msg.pattern');
    $this->logger->debug("Validing commit message with regex <comment>$pattern</comment>.");
    if (!preg_match($pattern, $message)) {
      $this->logger->error("Invalid commit message!");
      $this->say("Commit messages must conform to the regex $pattern");
      $this->logger->notice("To disable this command, see http://blt.rtfd.io/en/9.x/readme/extending-blt/#git-hooks");
      $this->logger->notice("To customize git hooks, see http://blt.rtfd.io/en/9.x/readme/extending-blt/#setupgit-hooks.");

      return 1;
    }

    return 0;
  }

  /**
   * Validates staged files.
   *
   * @command internal:git-hook:execute:pre-commit
   * @aliases git:pre-commit
   * @hidden
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
          'tests:phpcs:sniff:files' => ['file_list' => $changed_files],
          'tests:twig:lint:files' => ['file_list' => $changed_files],
          'tests:yaml:lint:files' => ['file_list' => $changed_files],
        ]);
      }
    );

    $changed_files_list = explode("\n", $changed_files);
    if (in_array('composer.json', $changed_files_list)
      || in_array('composer.lock', $changed_files_list)) {
      $collection->addCode(
        function () use ($changed_files) {
          return $this->invokeCommand('tests:composer:validate');
        }
      );
    }

    $result = $collection
      ->setVerbosityThreshold(VerbosityThresholdInterface::VERBOSITY_VERBOSE)
      ->run();

    if ($result->wasSuccessful()) {
      $this->say("<info>Your local code has passed git pre-commit validation.</info>");
    }

    return $result;
  }

}
