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
  public function commitMsgHook($message) {
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

  /**
   * @command git:pre-commit
   *
   * @param $changed_files
   */
  public function preCommitHook($changed_files) {


    // @todo See if $changed_files contains files in phpcs.filesets. Scan only those.
    // @todo Run this still right in PHPCS command, not here. pass string to it.
    $this->say("Sniffing staged files via PHP Code Sniffer...");
    $result = $this->invokeCommand('validate:phpcs:files', ['file_list' => $changed_files]);

//    // @todo See if $changed files contains twig files. Scan only those.
//    $this->say("Linting custom twig files...");
//    $result = $this->invokeCommand('validate:twig', $changed_files);
//
//    // @todo See if $changed_files contains yaml files. Scan only those.
//    $result = $this->invokeCommand('validate:yaml', $changed_files);
//
//    $this->say("Validating composer.json...");
//    // @todo See if $changed_files contains composer.* files. Scan only those.
//    $result = $this->invokeCommand('validate:composer', $changed_files);
  }

}
