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
   * @return int
   */
  public function preCommitHook($changed_files) {
    $this->invokeCommands([
      // Passing a file list to be PHPCS will cause all specified files to
      // be sniffed, regardless of the extensions or patterns defined in
      // phpcs.xml. So, we do not use validate:phpcs:files.
      'validate:phpcs' => [],
      'validate:twig:files' => ['file_list' => $changed_files],
      'validate:yaml:files' => ['file_list' => $changed_files],
    ]);

    $changed_files_list = explode("\n", $changed_files);
    if (in_array(['composer.json', 'composer.lock'], $changed_files_list)) {
      $this->invokeCommand('validate:composer', ['file_list' => $changed_files]);
    }

    $this->invokeHook('pre-commit');
    $this->say("<info>Your local code has passed git pre-commit validation.</info>");
  }

}
