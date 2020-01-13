<?php

namespace Acquia\Blt\Robo\Commands\Tests;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "tests:phpcs:sniff:all*" namespace.
 */
class PhpcsCommand extends BltTasks {

  /**
   * Executes PHP Code Sniffer against configured files.
   *
   * By default, these include custom themes, modules, and tests. This is
   * configured via phpcs.xml in the project root directory.
   *
   * @command tests:phpcs:sniff:all
   *
   * @aliases tpsa phpcs tests:phpcs:sniff validate:phpcs
   */
  public function sniffFileSets() {
    $exit_code = $this->doSniff();
    if ($exit_code) {
      if ($this->input()->isInteractive()) {
        $this->fixViolationsInteractively();
        throw new BltException("Initial execution of PHPCS failed. Re-run now that PHPCBF has fixed some violations.");
      }
      else {
        $this->logger->notice('Try running `blt source:fix:php-standards` to automatically fix standards violations.');
        throw new BltException("PHPCS failed.");
      }
    }
  }

  /**
   * Prompts user to fix PHPCS violations.
   */
  protected function fixViolationsInteractively() {
    $continue = $this->confirm("Attempt to fix violations automatically via PHPCBF?");
    if ($continue) {
      $this->invokeCommand('source:fix:php-standards');
      $this->logger->warning("You must stage any new changes to files before committing.");
    }
  }

  /**
   * Executes PHP Code Sniffer against a list of files.
   *
   * This command will execute PHP Codesniffer against a list of files. Note
   * that files excluded by phpcs.xml will not be sniffed, even if specifically
   * included here. However, files passed as arguments will be sniffed even if
   * they are _not_ specifically included/whitelisted in phpcs.xml.
   *
   * @param string $file_list
   *   A list of files to scan, separated by \n.
   *
   * @command tests:phpcs:sniff:files
   * @aliases tpsf
   *
   * @return int
   *   Exit code.
   */
  public function sniffFileList($file_list) {
    $this->say("Sniffing directories containing changed files...");
    $files = explode("\n", $file_list);
    $files = array_filter($files);
    if ($files) {
      $temp_path = $this->getConfigValue('repo.root') . '/tmp/phpcs-fileset';
      $this->taskWriteToFile($temp_path)
        ->lines($files)
        ->run();
      $arguments = "--file-list='$temp_path' -l";
      $exit_code = $this->doSniff($arguments);
      unlink($temp_path);

      return $exit_code;
    }

    return 0;
  }

  /**
   * Executes PHPCS against (unstaged) modified or untracked files in repo.
   *
   * This command will execute PHP Codesniffer against modified/untracked files
   * if those files are a subset of the phpcs.filesets filesets.
   *
   * @command tests:phpcs:sniff:modified
   * @aliases tpsm
   *
   * @return int
   *   Exit code.
   */
  public function sniffModified() {
    $this->say("Sniffing modified and untracked files in repo...");
    $arguments = "--filter=GitModified";
    $exit_code = $this->doSniff($arguments);

    return $exit_code;
  }

  /**
   * Executes PHPCS against staged files in repo.
   *
   * This command will execute PHP Codesniffer against staged files
   * if those files are a subset of the phpcs.filesets filesets.
   *
   * @command tests:phpcs:sniff:staged
   * @aliases tpss
   *
   * @return int
   *   Exit code.
   */
  public function sniffStaged() {
    $this->say("Sniffing staged files in repo...");
    $arguments = "--filter=GitStaged";
    $exit_code = $this->doSniff($arguments);

    return $exit_code;
  }

  /**
   * Executes PHP Code Sniffer using specified options/arguments.
   *
   * @param string $arguments
   *   The command arguments/options.
   *
   * @return int
   *   Exit code.
   */
  protected function doSniff($arguments = '') {
    $bin = $this->getConfigValue('composer.bin') . '/phpcs';
    $command = "'$bin' $arguments";
    if ($this->output()->isVerbose()) {
      $command .= ' -v';
    }
    elseif ($this->output()->isVeryVerbose()) {
      $command .= ' -vv';
    }
    $result = $this->taskExecStack()
      ->dir($this->getConfigValue('repo.root'))
      ->exec($command)
      ->printMetadata(FALSE)
      ->run();

    return $result->getExitCode();
  }

}
