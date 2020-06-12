<?php

namespace Acquia\Blt\Robo\Commands\Internal;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;
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
   *   Int.
   */
  public function commitMsgHook($message) {
    $this->say('Validating commit message syntax...');
    $pattern = $this->getConfigValue('git.commit-msg.pattern');
    $help_description = $this->getConfigValue('git.commit-msg.help_description');
    $example = $this->getConfigValue('git.commit-msg.example');
    $this->logger->debug("Validating commit message with regex <comment>$pattern</comment>.");
    if (!preg_match($pattern, $message)) {
      $this->logger->error("Invalid commit message!");
      $this->say("Commit messages must conform to the regex $pattern");
      if (!empty($help_description)) {
        $this->say("$help_description");
      }
      if (!empty($example)) {
        $this->say("Example: $example");
      }
      $this->logger->notice("To disable or customize Git hooks, see https://docs.acquia.com/blt/extending-blt/");

      return 1;
    }

    return 0;
  }

  /**
   * Validates staged files.
   *
   * @param string $changed_files
   *   A list of staged files, separated by \n.
   *
   * @command internal:git-hook:execute:pre-commit
   * @aliases git:pre-commit
   * @hidden
   *
   * @return \Robo\Result
   *   Result.
   */
  public function preCommitHook($changed_files) {
    $collection = $this->collectionBuilder();
    $collection->setProgressIndicator(NULL);
    $collection->addCode(
      function () use ($changed_files) {
        return $this->invokeCommands([
          'validate:twig:lint:files' => ['file_list' => $changed_files],
          'validate:yaml:lint:files' => ['file_list' => $changed_files],
        ]);
      }
    );

    $changed_files_list = explode("\n", $changed_files);
    if (in_array('composer.json', $changed_files_list)
      || in_array('composer.lock', $changed_files_list)) {
      $collection->addCode(
        function () {
          return $this->invokeCommand('validate:composer');
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

  /**
   * Validates staged files.
   *
   * @command internal:git-hook:execute:pre-push
   * @hidden
   */
  public function prePushHook() {
    try {
      $this->invokeCommand('validate');
    }
    catch (BltException $e) {
      $this->yell('Your code has failed pre-push validation.', 40, 'red');
      return 1;
    }

    $this->say("<info>Your local code has passed git pre-push validation.</info>");
  }

}
