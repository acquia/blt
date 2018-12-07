<?php

namespace Acquia\Blt\Robo\Commands\Validate;

use Acquia\Blt\Robo\BltTasks;
use Acquia\Blt\Robo\Exceptions\BltException;

/**
 * Defines commands in the "tests:phpcs:sniff:all*" namespace.
 */
class PhpcsCommand extends BltTasks {

  /**
   * Executes PHP Code Sniffer against all phpcs.filesets files.
   *
   * By default, these include custom themes, modules, and tests.
   *
   * @command tests:phpcs:sniff:all
   *
   * @aliases tpsa phpcs tests:phpcs:sniff validate:phpcs
   */
  public function sniffFileSets() {
    $bin = $this->getConfigValue('composer.bin');
    $result = $this->taskExecStack()
      ->dir($this->getConfigValue('repo.root'))
      ->exec("$bin/phpcs")
      ->run();
    $exit_code = $result->getExitCode();
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
   * Executes PHP Code Sniffer against a list of files, if in phpcs.filesets.
   *
   * This command will execute PHP Codesniffer against a list of files if those
   * files are a subset of the phpcs.filesets filesets.
   *
   * @command tests:phpcs:sniff:files
   * @aliases tpsf
   *
   * @param string $file_list
   *   A list of files to scan, separated by \n.
   *
   * @return int
   */
  public function sniffFileList($file_list) {
    $this->say("Sniffing directories containing changed files...");
    $files = explode("\n", $file_list);
    $files = array_filter($files);
    $exit_code = $this->doSniffFileList($files);

    return $exit_code;
  }

  /**
   * Executes PHP Code Sniffer against an array of files.
   *
   * @param array $files
   *   A flat array of absolute file paths.
   *
   * @return int
   */
  protected function doSniffFileList(array $files) {
    if ($files) {
      $temp_path = $this->getConfigValue('repo.root') . '/tmp/phpcs-fileset';
      $this->taskWriteToFile($temp_path)
        ->lines($files)
        ->run();

      $bin = $this->getConfigValue('composer.bin') . '/phpcs';
      $bootstrap = __DIR__ . "/phpcs-validate-files-bootstrap.php";
      $command = "'$bin' --file-list='$temp_path' --bootstrap='$bootstrap' -l";
      if ($this->output()->isVerbose()) {
        $command .= ' -v';
      }
      elseif ($this->output()->isVeryVerbose()) {
        $command .= ' -vv';
      }
      $result = $this->taskExecStack()
        ->exec($command)
        ->printMetadata(FALSE)
        ->run();

      unlink($temp_path);

      return $result->getExitCode();
    }

    return 0;
  }

}
